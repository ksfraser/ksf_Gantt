<?php

declare(strict_types=1);

namespace Ksfraser\Tests\Unit\Gantt\Service;

use DateTime;
use Ksfraser\Gantt\Entity\GanttChart;
use Ksfraser\Gantt\Entity\GanttTask;
use Ksfraser\Gantt\Service\GanttRenderer;
use PHPUnit\Framework\TestCase;

class GanttRendererTest extends TestCase
{
    private GanttRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new GanttRenderer();
    }

    public function testConstructorWithDefaultOptions(): void
    {
        $renderer = new GanttRenderer();

        $this->assertInstanceOf(GanttRenderer::class, $renderer);
    }

    public function testConstructorWithCustomOptions(): void
    {
        $renderer = new GanttRenderer([
            'dayWidth' => 60,
            'rowHeight' => 50,
            'headerHeight' => 60,
            'sidebarWidth' => 300,
        ]);

        $this->assertInstanceOf(GanttRenderer::class, $renderer);
    }

    public function testRenderHtmlReturnsHtmlString(): void
    {
        $chart = new GanttChart('chart-1', 'Test Chart');
        $task = new GanttTask(
            'task-1',
            'Test Task',
            new DateTime('2024-01-01'),
            new DateTime('2024-01-15')
        );
        $chart->addTask($task);

        $html = $this->renderer->renderHtml($chart);

        $this->assertIsString($html);
        $this->assertStringContainsString('gantt-container', $html);
        $this->assertStringContainsString('gantt-sidebar', $html);
        $this->assertStringContainsString('gantt-chart-area', $html);
    }

    public function testRenderHtmlContainsTaskNames(): void
    {
        $chart = new GanttChart('chart-1', 'Test Chart');
        $task = new GanttTask('task-1', 'My Test Task');
        $chart->addTask($task);

        $html = $this->renderer->renderHtml($chart);

        $this->assertStringContainsString('My Test Task', $html);
    }

    public function testRenderSvgReturnsSvgString(): void
    {
        $chart = new GanttChart('chart-1', 'Test Chart');
        $task = new GanttTask(
            'task-1',
            'Test Task',
            new DateTime('2024-01-01'),
            new DateTime('2024-01-15')
        );
        $chart->addTask($task);

        $svg = $this->renderer->renderSvg($chart);

        $this->assertIsString($svg);
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('xmlns=', $svg);
    }

    public function testToJsonReturnsJsonString(): void
    {
        $chart = new GanttChart('chart-1', 'Test Chart');
        $task = new GanttTask('task-1', 'Test Task');
        $chart->addTask($task);

        $json = $this->renderer->toJson($chart);

        $this->assertJson($json);
        $data = json_decode($json, true);
        $this->assertEquals('chart-1', $data['id']);
    }

    public function testToFullCalendarReturnsArrayOfEvents(): void
    {
        $chart = new GanttChart('chart-1', 'Test Chart');
        $task = new GanttTask(
            'task-1',
            'Test Task',
            new DateTime('2024-01-01'),
            new DateTime('2024-01-15')
        );
        $chart->addTask($task);

        $events = $this->renderer->toFullCalendar($chart);

        $this->assertIsArray($events);
        $this->assertNotEmpty($events);

        $event = $events[0];
        $this->assertArrayHasKey('id', $event);
        $this->assertArrayHasKey('title', $event);
        $this->assertArrayHasKey('start', $event);
        $this->assertArrayHasKey('end', $event);
        $this->assertArrayHasKey('progress', $event);
        $this->assertArrayHasKey('color', $event);
    }

    public function testToFullCalendarHandlesMilestones(): void
    {
        $chart = new GanttChart('chart-1', 'Test Chart');
        $milestone = new GanttTask('milestone-1', 'Milestone 1');
        $milestone->setMileStone(true);
        $milestone->setStartDate(new DateTime('2024-01-15'));
        $milestone->setEndDate(new DateTime('2024-01-15'));
        $chart->addTask($milestone);

        $events = $this->renderer->toFullCalendar($chart);

        $this->assertGreaterThanOrEqual(1, count($events));
        $hasMilestone = false;
        foreach ($events as $event) {
            if (str_contains($event['id'], 'milestone')) {
                $hasMilestone = true;
            }
        }
        $this->assertTrue($hasMilestone);
    }

    public function testRenderHtmlWithMultipleTasks(): void
    {
        $chart = new GanttChart('chart-1', 'Multi Task Chart');
        $chart->addTask(new GanttTask('task-1', 'Task 1'));
        $chart->addTask(new GanttTask('task-2', 'Task 2'));
        $chart->addTask(new GanttTask('task-3', 'Task 3'));

        $html = $this->renderer->renderHtml($chart);

        $this->assertStringContainsString('Task 1', $html);
        $this->assertStringContainsString('Task 2', $html);
        $this->assertStringContainsString('Task 3', $html);
    }

    public function testRenderHtmlWithEmptyChart(): void
    {
        $chart = new GanttChart('chart-1', 'Empty Chart');
        $chart->addTask(new GanttTask('task-1', 'Task 1', new DateTime(), new DateTime('+7 days')));

        $html = $this->renderer->renderHtml($chart);

        $this->assertStringContainsString('gantt-container', $html);
        $this->assertStringContainsString('Tasks', $html);
    }

    public function testRenderHtmlWithDefaultDateRange(): void
    {
        $chart = new GanttChart('chart-1', 'No Dates Chart');
        $chart->addTask(new GanttTask('task-1', 'Task 1', new DateTime(), new DateTime('+7 days')));

        $html = $this->renderer->renderHtml($chart);

        $this->assertIsString($html);
        $this->assertNotEmpty($html);
    }

    public function testToFullCalendarWithAssignee(): void
    {
        $chart = new GanttChart('chart-1', 'Test Chart');
        $task = new GanttTask(
            'task-1',
            'Test Task',
            new DateTime('2024-01-01'),
            new DateTime('2024-01-15')
        );
        $task->setAssignee('john@example.com');
        $chart->addTask($task);

        $events = $this->renderer->toFullCalendar($chart);

        $this->assertEquals('john@example.com', $events[0]['assignee']);
    }

    public function testRenderSvgWithTaskProgress(): void
    {
        $chart = new GanttChart('chart-1', 'Test Chart');
        $task = new GanttTask(
            'task-1',
            'Test Task',
            new DateTime('2024-01-01'),
            new DateTime('2024-01-15')
        );
        $task->setProgress(50);
        $chart->addTask($task);

        $svg = $this->renderer->renderSvg($chart);

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('rect', $svg);
    }
}