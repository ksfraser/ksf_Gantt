<?php
/**
 * ResourceUtilization Service
 *
 * Tracks and calculates resource allocation and utilization
 *
 * @package Ksfraser\Gantt\Service
 */

declare(strict_types=1);

namespace Ksfraser\Gantt\Service;

use Ksfraser\Gantt\Entity\GanttChart;
use Ksfraser\Gantt\Entity\GanttTask;
use DateTime;
use DateInterval;

class ResourceUtilization
{
    private array $resources;
    private float $maxDailyHours = 8.0;
    private float $warningThreshold = 0.8;
    private float $overloadThreshold = 1.0;

    public function __construct(array $options = [])
    {
        if (isset($options['maxDailyHours'])) {
            $this->maxDailyHours = (float) $options['maxDailyHours'];
        }
        if (isset($options['warningThreshold'])) {
            $this->warningThreshold = (float) $options['warningThreshold'];
        }
        if (isset($options['overloadThreshold'])) {
            $this->overloadThreshold = (float) $options['overloadThreshold'];
        }
    }

    public function calculateUtilization(GanttChart $chart, ?DateTime $startDate = null, ?DateTime $endDate = null): array
    {
        $startDate = $startDate ?? $chart->getStartDate() ?? new DateTime();
        $endDate = $endDate ?? $chart->getEndDate() ?? (clone $startDate)->add(new DateInterval('P30D'));

        $utilization = [];

        foreach ($chart->getAssignees() as $assignee) {
            $tasks = $chart->getTasksByAssignee($assignee);
            $assigneeUtilization = $this->calculateAssigneeUtilization(
                $assignee,
                $tasks,
                $startDate,
                $endDate
            );
            $utilization[$assignee] = $assigneeUtilization;
        }

        return $utilization;
    }

    public function getDailyUtilization(GanttChart $chart, string $assignee, DateTime $date): float
    {
        $tasks = $chart->getTasksByAssignee($assignee);
        $totalHours = 0.0;

        foreach ($tasks as $task) {
            if ($task->getStartDate() && $task->getEndDate()) {
                if ($date >= $task->getStartDate() && $date <= $task->getEndDate()) {
                    $duration = $task->getDuration() ?? 1;
                    $hoursPerDay = $task->getEstimatedHours() / max(1, $duration);
                    $totalHours += $hoursPerDay;
                }
            }
        }

        return $totalHours / $this->maxDailyHours;
    }

    public function getCapacityPlanning(GanttChart $chart, ?DateTime $startDate = null, ?DateTime $endDate = null): array
    {
        $startDate = $startDate ?? $chart->getStartDate() ?? new DateTime();
        $endDate = $endDate ?? $chart->getEndDate() ?? (clone $startDate)->add(new DateInterval('P30D'));

        $capacity = [];
        $currentDate = clone $startDate;

        while ($currentDate <= $endDate) {
            $dayCapacity = [
                'date' => $currentDate->format('Y-m-d'),
                'total_hours' => 0.0,
                'resources' => [],
            ];

            foreach ($chart->getAssignees() as $assignee) {
                $dailyUtil = $this->getDailyUtilization($chart, $assignee, $currentDate);
                $hours = $dailyUtil * $this->maxDailyHours;

                if ($hours > 0) {
                    $dayCapacity['resources'][$assignee] = [
                        'hours' => $hours,
                        'utilization' => $dailyUtil,
                        'status' => $this->getUtilizationStatus($dailyUtil),
                    ];
                    $dayCapacity['total_hours'] += $hours;
                }
            }

            $capacity[$currentDate->format('Y-m-d')] = $dayCapacity;
            $currentDate->modify('+1 day');
        }

        return $capacity;
    }

    public function getOverloadedResources(GanttChart $chart, ?DateTime $startDate = null, ?DateTime $endDate = null): array
    {
        $utilization = $this->calculateUtilization($chart, $startDate, $endDate);
        $overloaded = [];

        foreach ($utilization as $assignee => $data) {
            if ($data['overall_utilization'] > $this->overloadThreshold) {
                $overloaded[$assignee] = $data;
            }
        }

        return $overloaded;
    }

    public function getUnderutilizedResources(GanttChart $chart, ?DateTime $startDate = null, ?DateTime $endDate = null): array
    {
        $utilization = $this->calculateUtilization($chart, $startDate, $endDate);
        $underutilized = [];

        foreach ($utilization as $assignee => $data) {
            if ($data['overall_utilization'] < $this->warningThreshold) {
                $underutilized[$assignee] = $data;
            }
        }

        return $underutilized;
    }

    public function getResourceWorkload(GanttChart $chart): array
    {
        $workload = [];

        foreach ($chart->getAssignees() as $assignee) {
            $tasks = $chart->getTasksByAssignee($assignee);

            $totalEstimated = 0.0;
            $totalActual = 0.0;
            $completedCount = 0;
            $inProgressCount = 0;
            $pendingCount = 0;

            foreach ($tasks as $task) {
                $totalEstimated += $task->getEstimatedHours();
                $totalActual += $task->getActualHours();

                if ($task->isCompleted()) {
                    $completedCount++;
                } elseif ($task->getStatus() === 'in_progress') {
                    $inProgressCount++;
                } else {
                    $pendingCount++;
                }
            }

            $workload[$assignee] = [
                'task_count' => count($tasks),
                'completed' => $completedCount,
                'in_progress' => $inProgressCount,
                'pending' => $pendingCount,
                'estimated_hours' => $totalEstimated,
                'actual_hours' => $totalActual,
                'efficiency' => $totalEstimated > 0 ? ($totalActual / $totalEstimated) : 0,
            ];
        }

        return $workload;
    }

    private function calculateAssigneeUtilization(string $assignee, array $tasks, DateTime $startDate, DateTime $endDate): array
    {
        $totalHours = 0.0;
        $workingDays = 0;
        $completedHours = 0.0;

        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            if (!in_array($currentDate->format('N'), ['6', '7'])) {
                $workingDays++;
            }
            $currentDate->modify('+1 day');
        }

        $taskStats = [
            'completed' => 0,
            'in_progress' => 0,
            'pending' => 0,
        ];

        foreach ($tasks as $task) {
            $totalHours += $task->getEstimatedHours();
            $completedHours += $task->getActualHours();

            if ($task->isCompleted()) {
                $taskStats['completed']++;
            } elseif ($task->getStatus() === 'in_progress') {
                $taskStats['in_progress']++;
            } else {
                $taskStats['pending']++;
            }
        }

        $availableHours = $workingDays * $this->maxDailyHours;
        $overallUtilization = $availableHours > 0 ? ($totalHours / $availableHours) : 0;

        return [
            'assignee' => $assignee,
            'total_hours' => $totalHours,
            'completed_hours' => $completedHours,
            'available_hours' => $availableHours,
            'working_days' => $workingDays,
            'overall_utilization' => $overallUtilization,
            'status' => $this->getUtilizationStatus($overallUtilization),
            'tasks' => $taskStats,
        ];
    }

    private function getUtilizationStatus(float $utilization): string
    {
        if ($utilization > $this->overloadThreshold) {
            return 'overloaded';
        }
        if ($utilization >= $this->warningThreshold) {
            return 'optimal';
        }
        if ($utilization > 0) {
            return 'underutilized';
        }
        return 'free';
    }

    public function exportToCsv(GanttChart $chart, ?DateTime $startDate = null, ?DateTime $endDate = null): string
    {
        $utilization = $this->calculateUtilization($chart, $startDate, $endDate);

        $csv = "Assignee,Total Hours,Available Hours,Utilization,Status,Completed Tasks,In Progress Tasks,Pending Tasks\n";

        foreach ($utilization as $assignee => $data) {
            $csv .= sprintf(
                '"%s",%.2f,%.2f,%.1f%%,%s,%d,%d,%d\n',
                $assignee,
                $data['total_hours'],
                $data['available_hours'],
                $data['overall_utilization'] * 100,
                $data['status'],
                $data['tasks']['completed'],
                $data['tasks']['in_progress'],
                $data['tasks']['pending']
            );
        }

        return $csv;
    }

    public function toJson(GanttChart $chart, ?DateTime $startDate = null, ?DateTime $endDate = null): string
    {
        return json_encode([
            'utilization' => $this->calculateUtilization($chart, $startDate, $endDate),
            'workload' => $this->getResourceWorkload($chart),
            'capacity_planning' => $this->getCapacityPlanning($chart, $startDate, $endDate),
        ], JSON_PRETTY_PRINT);
    }
}