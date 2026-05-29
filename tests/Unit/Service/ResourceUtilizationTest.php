<?php

declare(strict_types=1);

namespace Ksfraser\Tests\Unit\Gantt\Service;

use DateTime;
use Ksfraser\Gantt\Entity\GanttChart;
use Ksfraser\Gantt\Entity\GanttTask;
use Ksfraser\Gantt\Service\ResourceUtilization;
use PHPUnit\Framework\TestCase;

class ResourceUtilizationTest extends TestCase
{
    private ResourceUtilization $utilization;

    protected function setUp(): void
    {
        $this->utilization = new ResourceUtilization();
    }

    public function testConstructorWithDefaultOptions(): void
    {
        $utilization = new ResourceUtilization();

        $this->assertInstanceOf(ResourceUtilization::class, $utilization);
    }

    public function testConstructorWithCustomOptions(): void
    {
        $utilization = new ResourceUtilization([
            'maxDailyHours' => 6.0,
            'warningThreshold' => 0.7,
            'overloadThreshold' => 0.9,
        ]);

        $this->assertInstanceOf(ResourceUtilization::class, $utilization);
    }

    public function testCalculateUtilizationReturnsArray(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $task = $this->createMockTaskWithHours('task-1', 'Task 1', 'alice', 8, 4);
        $chart->addTask($task);

        $utilization = $this->utilization->calculateUtilization($chart, new DateTime(), new DateTime('+7 days'));

        $this->assertIsArray($utilization);
    }

    public function testCalculateUtilizationGroupsByAssignee(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $task1 = $this->createMockTaskWithHours('task-1', 'Task 1', 'alice', 8, 4);
        $task2 = $this->createMockTaskWithHours('task-2', 'Task 2', 'bob', 6, 2);
        $chart->addTask($task1);
        $chart->addTask($task2);

        $utilization = $this->utilization->calculateUtilization($chart, new DateTime(), new DateTime('+7 days'));

        $this->assertArrayHasKey('alice', $utilization);
        $this->assertArrayHasKey('bob', $utilization);
    }

    private function createMockTaskWithHours(string $id, string $name, string $assignee, float $estimated, float $actual): GanttTask
    {
        $task = new GanttTask($id, $name, new DateTime(), new DateTime('+7 days'));
        $task->setAssignee($assignee);
        
        $class = new \ReflectionClass(GanttTask::class);
        
        $estimatedProp = $class->getProperty('estimatedHours');
        $estimatedProp->setAccessible(true);
        $estimatedProp->setValue($task, $estimated);
        
        $actualProp = $class->getProperty('actualHours');
        $actualProp->setAccessible(true);
        $actualProp->setValue($task, $actual);
        
        return $task;
    }

    public function testGetDailyUtilizationReturnsFloat(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $task = $this->createMockTaskWithHours('task-1', 'Task 1', 'alice', 8, 4);
        $task->setStartDate(new DateTime('2024-01-01'));
        $task->setEndDate(new DateTime('2024-01-05'));
        $chart->addTask($task);

        $date = new DateTime('2024-01-03');
        $util = $this->utilization->getDailyUtilization($chart, 'alice', $date);

        $this->assertIsFloat($util);
        $this->assertGreaterThanOrEqual(0, $util);
    }

    public function testGetCapacityPlanningReturnsArray(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $task = $this->createMockTaskWithHours('task-1', 'Task 1', 'alice', 8, 4);
        $task->setStartDate(new DateTime('2024-01-01'));
        $task->setEndDate(new DateTime('2024-01-05'));
        $chart->addTask($task);

        $startDate = new DateTime('2024-01-01');
        $endDate = new DateTime('2024-01-03');
        $capacity = $this->utilization->getCapacityPlanning($chart, $startDate, $endDate);

        $this->assertIsArray($capacity);
        $this->assertArrayHasKey('2024-01-01', $capacity);
        $this->assertArrayHasKey('2024-01-02', $capacity);
        $this->assertArrayHasKey('2024-01-03', $capacity);
    }

    public function testGetOverloadedResourcesReturnsArray(): void
    {
        $chart = new GanttChart('chart-1', 'Test');

        $overloaded = $this->utilization->getOverloadedResources($chart, new DateTime(), new DateTime('+7 days'));

        $this->assertIsArray($overloaded);
    }

    public function testGetUnderutilizedResourcesReturnsArray(): void
    {
        $chart = new GanttChart('chart-1', 'Test');

        $underutilized = $this->utilization->getUnderutilizedResources($chart, new DateTime(), new DateTime('+7 days'));

        $this->assertIsArray($underutilized);
    }

    public function testGetResourceWorkloadReturnsArray(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $task = $this->createMockTaskWithHours('task-1', 'Task 1', 'alice', 8, 4);
        $task->setStatus('in_progress');
        $chart->addTask($task);

        $workload = $this->utilization->getResourceWorkload($chart);

        $this->assertIsArray($workload);
        $this->assertArrayHasKey('alice', $workload);

        $aliceWorkload = $workload['alice'];
        $this->assertArrayHasKey('task_count', $aliceWorkload);
        $this->assertArrayHasKey('completed', $aliceWorkload);
        $this->assertArrayHasKey('in_progress', $aliceWorkload);
        $this->assertArrayHasKey('pending', $aliceWorkload);
    }

    public function testExportToCsvReturnsString(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $task = $this->createMockTaskWithHours('task-1', 'Task 1', 'alice', 8, 4);
        $chart->addTask($task);

        $csv = $this->utilization->exportToCsv($chart, new DateTime(), new DateTime('+7 days'));

        $this->assertIsString($csv);
        $this->assertStringContainsString('Assignee', $csv);
        $this->assertStringContainsString('Total Hours', $csv);
    }

    public function testExportToCsvWithAssignee(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $task = $this->createMockTaskWithHours('task-1', 'Task 1', 'alice', 8, 4);
        $chart->addTask($task);

        $csv = $this->utilization->exportToCsv($chart, new DateTime(), new DateTime('+7 days'));

        $this->assertStringContainsString('alice', $csv);
    }

    public function testToJsonReturnsValidJson(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $chart->addTask(new GanttTask('task-1', 'Task 1', new DateTime(), new DateTime('+7 days')));

        $json = $this->utilization->toJson($chart, new DateTime(), new DateTime('+7 days'));

        $this->assertJson($json);
        $data = json_decode($json, true);
        $this->assertArrayHasKey('utilization', $data);
        $this->assertArrayHasKey('workload', $data);
        $this->assertArrayHasKey('capacity_planning', $data);
    }

    public function testCalculateUtilizationWithEmptyChart(): void
    {
        $chart = new GanttChart('chart-1', 'Empty Chart');

        $utilization = $this->utilization->calculateUtilization($chart, new DateTime(), new DateTime('+7 days'));

        $this->assertIsArray($utilization);
        $this->assertEmpty($utilization);
    }

    public function testGetCapacityPlanningWithNoDatesUsesChartDates(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $chart->addTask(new GanttTask(
            'task-1',
            'Task 1',
            new DateTime('2024-01-01'),
            new DateTime('2024-01-05')
        ));

        $capacity = $this->utilization->getCapacityPlanning($chart, new DateTime('2024-01-01'), new DateTime('2024-01-03'));

        $this->assertIsArray($capacity);
    }
}