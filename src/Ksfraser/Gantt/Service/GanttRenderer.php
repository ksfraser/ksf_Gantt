<?php
/**
 * GanttRenderer Service
 *
 * Renders Gantt charts to HTML, SVG, or JSON
 *
 * @package Ksfraser\Gantt\Service
 */

declare(strict_types=1);

namespace Ksfraser\Gantt\Service;

use Ksfraser\Gantt\Entity\GanttTask;
use Ksfraser\Gantt\Entity\GanttChart;
use DateTime;
use DateInterval;

class GanttRenderer
{
    private int $dayWidth = 40;
    private int $rowHeight = 40;
    private int $headerHeight = 50;
    private int $sidebarWidth = 250;
    private string $primaryColor = '#3b82f6';
    private string $completedColor = '#22c55e';
    private string $overdueColor = '#ef4444';
    private string $inProgressColor = '#f59e0b';

    public function __construct(array $options = [])
    {
        if (isset($options['dayWidth'])) {
            $this->dayWidth = (int) $options['dayWidth'];
        }
        if (isset($options['rowHeight'])) {
            $this->rowHeight = (int) $options['rowHeight'];
        }
        if (isset($options['headerHeight'])) {
            $this->headerHeight = (int) $options['headerHeight'];
        }
        if (isset($options['sidebarWidth'])) {
            $this->sidebarWidth = (int) $options['sidebarWidth'];
        }
    }

    public function renderHtml(GanttChart $chart, array $options = []): string
    {
        $startDate = $chart->getStartDate() ?? new DateTime();
        $endDate = $chart->getEndDate() ?? (clone $startDate)->add(new DateInterval('P30D'));

        $totalDays = (int) $startDate->diff($endDate)->days + 1;
        $chartWidth = $totalDays * $this->dayWidth;
        $chartHeight = count($chart->getTasks()) * $this->rowHeight;

        $html = $this->renderStyles();
        $html .= '<div class="gantt-container">';
        $html .= '<div class="gantt-sidebar" style="width: ' . $this->sidebarWidth . 'px;">';
        $html .= $this->renderSidebar($chart);
        $html .= '</div>';
        $html .= '<div class="gantt-chart-wrapper">';
        $html .= '<div class="gantt-timeline-header" style="height: ' . $this->headerHeight . 'px; margin-left: ' . $this->sidebarWidth . 'px;">';
        $html .= $this->renderTimelineHeader($startDate, $totalDays);
        $html .= '</div>';
        $html .= '<div class="gantt-chart-area" style="margin-left: ' . $this->sidebarWidth . 'px;">';
        $html .= '<div class="gantt-chart" style="width: ' . $chartWidth . 'px; height: ' . $chartHeight . 'px;">';
        $html .= $this->renderGrid($startDate, $totalDays, count($chart->getTasks()));
        $html .= $this->renderTasks($chart, $startDate);
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function renderSvg(GanttChart $chart): string
    {
        $startDate = $chart->getStartDate() ?? new DateTime();
        $endDate = $chart->getEndDate() ?? (clone $startDate)->add(new DateInterval('P30D'));

        $totalDays = (int) $startDate->diff($endDate)->days + 1;
        $chartWidth = $totalDays * $this->dayWidth;
        $chartHeight = count($chart->getTasks()) * $this->rowHeight;

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">',
            $chartWidth + $this->sidebarWidth,
            $chartHeight + $this->headerHeight,
            $chartWidth + $this->sidebarWidth,
            $chartHeight + $this->headerHeight
        );

        $svg .= $this->renderSvgGrid($startDate, $totalDays, count($chart->getTasks()));
        $svg .= $this->renderSvgTasks($chart, $startDate);
        $svg .= '</svg>';

        return $svg;
    }

    public function toJson(GanttChart $chart): string
    {
        return $chart->toJson();
    }

    public function toFullCalendar(GanttChart $chart): array
    {
        $events = [];
        foreach ($chart->getTasks() as $task) {
            if ($task->getStartDate() && $task->getEndDate()) {
                $events[] = [
                    'id' => $task->getId(),
                    'title' => $task->getName(),
                    'start' => $task->getStartDate()->format('Y-m-d'),
                    'end' => $task->getEndDate()->format('Y-m-d'),
                    'progress' => $task->getProgress(),
                    'color' => $this->getTaskColor($task),
                    'assignee' => $task->getAssignee(),
                ];
            }
            if ($task->isMileStone() && $task->getStartDate()) {
                $events[] = [
                    'id' => $task->getId() . '_milestone',
                    'title' => '★ ' . $task->getName(),
                    'start' => $task->getStartDate()->format('Y-m-d'),
                    'allDay' => true,
                    'color' => $task->getColor(),
                ];
            }
        }
        return $events;
    }

    private function renderStyles(): string
    {
        return <<<CSS
<style>
.gantt-container {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    overflow-x: auto;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
}
.gantt-sidebar {
    position: absolute;
    background: #f9fafb;
    border-right: 1px solid #e5e7eb;
    z-index: 10;
}
.gantt-sidebar-header {
    height: 50px;
    display: flex;
    align-items: center;
    padding: 0 16px;
    font-weight: 600;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}
.gantt-task-name {
    height: 40px;
    display: flex;
    align-items: center;
    padding: 0 16px;
    border-bottom: 1px solid #f3f4f6;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.gantt-timeline-header {
    position: sticky;
    top: 0;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    overflow: hidden;
    z-index: 5;
}
.gantt-day-header {
    position: absolute;
    width: {$this->dayWidth}px;
    height: 50px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    border-right: 1px solid #f3f4f6;
}
.gantt-day-header .day { font-weight: 600; font-size: 14px; }
.gantt-day-header .weekday { color: #6b7280; }
.gantt-chart {
    position: relative;
    background: #fff;
}
.gantt-grid-row {
    position: absolute;
    height: {$this->rowHeight}px;
    border-bottom: 1px solid #f3f4f6;
    width: 100%;
}
.gantt-grid-cell {
    position: absolute;
    width: {$this->dayWidth}px;
    height: 100%;
    border-right: 1px solid #f3f4f6;
}
.gantt-grid-cell.weekend { background: #f9fafb; }
.gantt-task-bar {
    position: absolute;
    height: 28px;
    top: 6px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    padding: 0 8px;
    font-size: 12px;
    color: white;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}
.gantt-task-bar:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.gantt-task-bar.completed { background: {$this->completedColor}; }
.gantt-task-bar.in-progress { background: {$this->inProgressColor}; }
.gantt-task-bar.overdue { background: {$this->overdueColor}; }
.gantt-task-bar.pending { background: {$this->primaryColor}; }
.gantt-milestone {
    position: absolute;
    width: 20px;
    height: 20px;
    background: {$this->primaryColor};
    transform: rotate(45deg);
    top: 10px;
}
.gantt-progress-bar {
    height: 4px;
    background: rgba(255,255,255,0.3);
    border-radius: 2px;
    margin-top: 4px;
    overflow: hidden;
}
.gantt-progress-fill {
    height: 100%;
    background: white;
    border-radius: 2px;
}
</style>
CSS;
    }

    private function renderSidebar(GanttChart $chart): string
    {
        $html = '<div class="gantt-sidebar-header">Tasks</div>';
        foreach ($chart->getTasks() as $task) {
            $html .= '<div class="gantt-task-name" title="' . htmlspecialchars($task->getName()) . '">';
            $html .= htmlspecialchars($task->getName());
            $html .= '</div>';
        }
        return $html;
    }

    private function renderTimelineHeader(DateTime $startDate, int $totalDays): string
    {
        $html = '';
        $currentDate = clone $startDate;
        for ($i = 0; $i < $totalDays; $i++) {
            $dayNum = (int) $currentDate->format('d');
            $weekday = $currentDate->format('D');
            $isWeekend = in_array($currentDate->format('N'), ['6', '7']);

            $html .= '<div class="gantt-day-header' . ($isWeekend ? ' weekend' : '') . '" style="left: ' . ($i * $this->dayWidth) . 'px;">';
            $html .= '<span class="day">' . $dayNum . '</span>';
            $html .= '<span class="weekday">' . $weekday . '</span>';
            $html .= '</div>';

            $currentDate->modify('+1 day');
        }
        return $html;
    }

    private function renderGrid(DateTime $startDate, int $totalDays, int $taskCount): string
    {
        $html = '';
        for ($row = 0; $row < $taskCount; $row++) {
            $y = $row * $this->rowHeight;
            $html .= '<div class="gantt-grid-row" style="top: ' . $y . 'px;">';

            $currentDate = clone $startDate;
            for ($col = 0; $col < $totalDays; $col++) {
                $isWeekend = in_array($currentDate->format('N'), ['6', '7']);
                $html .= '<div class="gantt-grid-cell' . ($isWeekend ? ' weekend' : '') . '" style="left: ' . ($col * $this->dayWidth) . 'px;">';
                $html .= '</div>';
                $currentDate->modify('+1 day');
            }
            $html .= '</div>';
        }
        return $html;
    }

    private function renderTasks(GanttChart $chart, DateTime $startDate): string
    {
        $html = '';
        $row = 0;
        foreach ($chart->getTasks() as $task) {
            $y = $row * $this->rowHeight;

            if ($task->isMileStone() && $task->getStartDate()) {
                $x = (int) $startDate->diff($task->getStartDate())->days * $this->dayWidth;
                $html .= '<div class="gantt-milestone" style="left: ' . $x . 'px;" title="' . htmlspecialchars($task->getName()) . '"></div>';
            } elseif ($task->getStartDate() && $task->getEndDate()) {
                $x = (int) $startDate->diff($task->getStartDate())->days * $this->dayWidth;
                $width = max($this->dayWidth, (int) $startDate->diff($task->getEndDate())->days * $this->dayWidth);
                $statusClass = $this->getTaskStatusClass($task);
                $color = $task->getColor() ?: $this->getTaskColor($task);

                $html .= '<div class="gantt-task-bar ' . $statusClass . '" style="left: ' . $x . 'px; width: ' . $width . 'px; top: ' . ($y + 6) . 'px; background: ' . $color . ';" title="' . htmlspecialchars($task->getName()) . ' (' . $task->getProgress() . '%)">';
                $html .= '<span>' . htmlspecialchars($task->getName()) . '</span>';
                if ($task->getProgress() > 0 && $task->getProgress() < 100) {
                    $html .= '<div class="gantt-progress-bar"><div class="gantt-progress-fill" style="width: ' . $task->getProgress() . '%;"></div></div>';
                }
                $html .= '</div>';
            }
            $row++;
        }
        return $html;
    }

    private function renderSvgGrid(DateTime $startDate, int $totalDays, int $taskCount): string
    {
        $svg = '';
        for ($row = 0; $row < $taskCount; $row++) {
            $y = $row * $this->rowHeight;
            $svg .= '<rect x="0" y="' . $y . '" width="' . ($totalDays * $this->dayWidth) . '" height="' . $this->rowHeight . '" fill="' . ($row % 2 === 0 ? '#fff' : '#f9fafb') . '"/>';
        }

        $currentDate = clone $startDate;
        for ($col = 0; $col < $totalDays; $col++) {
            $x = $col * $this->dayWidth;
            $isWeekend = in_array($currentDate->format('N'), ['6', '7']);
            if ($isWeekend) {
                $svg .= '<rect x="' . $x . '" y="0" width="' . $this->dayWidth . '" height="' . ($taskCount * $this->rowHeight) . '" fill="#f3f4f6" opacity="0.5"/>';
            }
            $currentDate->modify('+1 day');
        }

        return $svg;
    }

    private function renderSvgTasks(GanttChart $chart, DateTime $startDate): string
    {
        $svg = '';
        $row = 0;
        foreach ($chart->getTasks() as $task) {
            $y = $row * $this->rowHeight + 6;

            if ($task->getStartDate() && $task->getEndDate()) {
                $x = (int) $startDate->diff($task->getStartDate())->days * $this->dayWidth;
                $width = max($this->dayWidth, (int) $startDate->diff($task->getEndDate())->days * $this->dayWidth);
                $color = $task->getColor() ?: $this->getTaskColor($task);

                $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $width . '" height="28" rx="4" fill="' . $color . '"/>';
                $svg .= '<text x="' . ($x + 8) . '" y="' . ($y + 18) . '" fill="white" font-size="12">' . htmlspecialchars($task->getName()) . '</text>';
            }
            $row++;
        }

        return $svg;
    }

    private function getTaskColor(GanttTask $task): string
    {
        if ($task->isCompleted()) {
            return $this->completedColor;
        }
        if ($task->isOverdue()) {
            return $this->overdueColor;
        }
        if ($task->getStatus() === 'in_progress') {
            return $this->inProgressColor;
        }
        return $task->getColor() ?: $this->primaryColor;
    }

    private function getTaskStatusClass(GanttTask $task): string
    {
        if ($task->isCompleted()) {
            return 'completed';
        }
        if ($task->isOverdue()) {
            return 'overdue';
        }
        if ($task->getStatus() === 'in_progress') {
            return 'in-progress';
        }
        return 'pending';
    }
}