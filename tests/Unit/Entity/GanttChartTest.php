<?php

declare(strict_types=1);

namespace Ksfraser\Tests\Unit\Gantt\Entity;

use DateTime;
use Ksfraser\Gantt\Entity\GanttChart;
use Ksfraser\Gantt\Entity\GanttTask;
use PHPUnit\Framework\TestCase;

class GanttChartTest extends TestCase
{
    public function testConstructorInitializesWithIdAndName(): void
    {
        $chart = new GanttChart('chart-1', 'Test Chart');

        $this->assertEquals('chart-1', $chart->getId());
        $this->assertEquals('Test Chart', $chart->getName());
        $this->assertEquals([], $chart->getTasks());
        $this->assertEquals('UTC', $chart->getTimezone());
    }

    public function testSetNameUpdatesChartName(): void
    {
        $chart = new GanttChart('chart-1', 'Original');
        $result = $chart->setName('Updated');

        $this->assertEquals('Updated', $chart->getName());
        $this->assertSame($chart, $result);
    }

    public function testSetTimezoneUpdatesTimezone(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $chart->setTimezone('America/New_York');

        $this->assertEquals('America/New_York', $chart->getTimezone());
    }

    public function testAddTaskInsertsTaskAndReturnsSelf(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $task = new GanttTask('task-1', 'Task 1');

        $result = $chart->addTask($task);

        $this->assertCount(1, $chart->getTasks());
        $this->assertTrue($chart->hasTask('task-1'));
        $this->assertSame($chart, $result);
    }

    public function testAddTaskUpdatesDateRange(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $task = new GanttTask(
            'task-1',
            'Task 1',
            new DateTime('2024-01-01'),
            new DateTime('2024-01-15')
        );

        $chart->addTask($task);

        $this->assertEquals('2024-01-01', $chart->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2024-01-15', $chart->getEndDate()->format('Y-m-d'));
    }

    public function testRemoveTaskRemovesTaskAndReturnsSelf(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $task = new GanttTask('task-1', 'Task 1');
        $chart->addTask($task);

        $result = $chart->removeTask('task-1');

        $this->assertCount(0, $chart->getTasks());
        $this->assertFalse($chart->hasTask('task-1'));
        $this->assertSame($chart, $result);
    }

    public function testRemoveTaskRemovesTaskFromDependencies(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $task1 = new GanttTask('task-1', 'Task 1');
        $task2 = new GanttTask('task-2', 'Task 2');
        $task2->addDependency('task-1');

        $chart->addTask($task1);
        $chart->addTask($task2);
        $chart->removeTask('task-1');

        $this->assertNotContains('task-1', $task2->getDependencies());
    }

    public function testGetTaskReturnsTaskOrNull(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $task = new GanttTask('task-1', 'Task 1');
        $chart->addTask($task);

        $this->assertSame($task, $chart->getTask('task-1'));
        $this->assertNull($chart->getTask('non-existent'));
    }

    public function testGetTaskCountReturnsCorrectCount(): void
    {
        $chart = new GanttChart('chart-1', 'Test');

        $this->assertEquals(0, $chart->getTaskCount());

        $chart->addTask(new GanttTask('task-1', 'Task 1'));
        $chart->addTask(new GanttTask('task-2', 'Task 2'));

        $this->assertEquals(2, $chart->getTaskCount());
    }

    public function testGetCompletedCountReturnsCorrectCount(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $task1 = new GanttTask('task-1', 'Task 1');
        $task1->setProgress(100);

        $task2 = new GanttTask('task-2', 'Task 2');
        $task2->setProgress(50);

        $task3 = new GanttTask('task-3', 'Task 3');
        $task3->setStatus('completed');

        $chart->addTask($task1);
        $chart->addTask($task2);
        $chart->addTask($task3);

        $this->assertEquals(2, $chart->getCompletedCount());
    }

    public function testGetOverallProgressReturnsAverageProgress(): void
    {
        $chart = new GanttChart('chart-1', 'Test');

        $task1 = new GanttTask('task-1', 'Task 1');
        $task1->setProgress(50);

        $task2 = new GanttTask('task-2', 'Task 2');
        $task2->setProgress(100);

        $chart->addTask($task1);
        $chart->addTask($task2);

        $this->assertEquals(75.0, $chart->getOverallProgress());
    }

    public function testGetOverallProgressReturnsZeroWhenNoTasks(): void
    {
        $chart = new GanttChart('chart-1', 'Test');

        $this->assertEquals(0.0, $chart->getOverallProgress());
    }

    public function testGetTasksByStatusFiltersTasks(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $task1 = new GanttTask('task-1', 'Task 1');
        $task1->setStatus('in_progress');

        $task2 = new GanttTask('task-2', 'Task 2');
        $task2->setStatus('completed');

        $task3 = new GanttTask('task-3', 'Task 3');
        $task3->setStatus('in_progress');

        $chart->addTask($task1);
        $chart->addTask($task2);
        $chart->addTask($task3);

        $filtered = $chart->getTasksByStatus('in_progress');

        $this->assertCount(2, $filtered);
    }

    public function testGetTasksByAssigneeFiltersTasks(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $task1 = new GanttTask('task-1', 'Task 1');
        $task1->setAssignee('alice');

        $task2 = new GanttTask('task-2', 'Task 2');
        $task2->setAssignee('bob');

        $chart->addTask($task1);
        $chart->addTask($task2);

        $filtered = $chart->getTasksByAssignee('alice');

        $this->assertCount(1, $filtered);
        $this->assertSame($task1, reset($filtered));
    }

    public function testGetRootTasksReturnsTasksWithoutParent(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $task1 = new GanttTask('task-1', 'Task 1');
        $task2 = new GanttTask('task-2', 'Task 2');
        $task2->setParentId('task-1');

        $chart->addTask($task1);
        $chart->addTask($task2);

        $rootTasks = $chart->getRootTasks();

        $this->assertCount(1, $rootTasks);
        $this->assertSame($task1, reset($rootTasks));
    }

    public function testGetSubtasksReturnsTasksWithParentId(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $task1 = new GanttTask('task-1', 'Task 1');
        $task2 = new GanttTask('task-2', 'Task 2');
        $task2->setParentId('task-1');

        $chart->addTask($task1);
        $chart->addTask($task2);

        $subtasks = $chart->getSubtasks('task-1');

        $this->assertCount(1, $subtasks);
        $this->assertSame($task2, reset($subtasks));
    }

    public function testGetAssigneesReturnsUniqueAssigneeList(): void
    {
        $chart = new GanttChart('chart-1', 'Test');
        $task1 = new GanttTask('task-1', 'Task 1');
        $task1->setAssignee('alice');

        $task2 = new GanttTask('task-2', 'Task 2');
        $task2->setAssignee('bob');

        $task3 = new GanttTask('task-3', 'Task 3');
        $task3->setAssignee('alice');

        $chart->addTask($task1);
        $chart->addTask($task2);
        $chart->addTask($task3);

        $assignees = $chart->getAssignees();

        $this->assertCount(2, $assignees);
        $this->assertContains('alice', $assignees);
        $this->assertContains('bob', $assignees);
    }

    public function testHasDependencyCycleReturnsTrueForCircularDependencies(): void
    {
        $chart = new GanttChart('chart-1', 'Test');

        $task1 = new GanttTask('task-1', 'Task 1');
        $task1->addDependency('task-2');

        $task2 = new GanttTask('task-2', 'Task 2');
        $task2->addDependency('task-1');

        $chart->addTask($task1);
        $chart->addTask($task2);

        $this->assertTrue($chart->hasDependencyCycle());
    }

    public function testHasDependencyCycleReturnsFalseForNoCycles(): void
    {
        $chart = new GanttChart('chart-1', 'Test');

        $task1 = new GanttTask('task-1', 'Task 1');
        $task2 = new GanttTask('task-2', 'Task 2');
        $task2->addDependency('task-1');

        $chart->addTask($task1);
        $chart->addTask($task2);

        $this->assertFalse($chart->hasDependencyCycle());
    }

    public function testTopologicalSortReturnsOrderedTasks(): void
    {
        $chart = new GanttChart('chart-1', 'Test');

        $task1 = new GanttTask('task-1', 'Task 1');
        $task2 = new GanttTask('task-2', 'Task 2');
        $task2->addDependency('task-1');
        $task3 = new GanttTask('task-3', 'Task 3');
        $task3->addDependency('task-2');

        $chart->addTask($task1);
        $chart->addTask($task2);
        $chart->addTask($task3);

        $sorted = $chart->topologicalSort();

        $this->assertEquals(['task-1', 'task-2', 'task-3'], $sorted);
    }

    public function testToArrayReturnsCorrectStructure(): void
    {
        $chart = new GanttChart('chart-1', 'Test Chart');
        $task = new GanttTask('task-1', 'Task 1');
        $task->setProgress(50);
        $chart->addTask($task);

        $array = $chart->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('start_date', $array);
        $this->assertArrayHasKey('end_date', $array);
        $this->assertArrayHasKey('timezone', $array);
        $this->assertArrayHasKey('tasks', $array);
        $this->assertArrayHasKey('task_count', $array);
        $this->assertArrayHasKey('completed_count', $array);
        $this->assertArrayHasKey('overall_progress', $array);
        $this->assertEquals(1, $array['task_count']);
    }

public function testToJsonReturnsValidJson(): void
    {
        $chart = new GanttChart('chart-1', 'Test Chart');
        $task = new GanttTask('task-1', 'Test Task');
        $task->setStartDate(new DateTime());
        $task->setEndDate(new DateTime('+7 days'));
        $chart->addTask($task);

        $json = $chart->toJson();

        $this->assertJson($json);
        $data = json_decode($json, true);
        $this->assertEquals('chart-1', $data['id']);
    }
}