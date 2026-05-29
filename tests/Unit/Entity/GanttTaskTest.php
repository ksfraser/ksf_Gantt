<?php

declare(strict_types=1);

namespace Ksfraser\Tests\Unit\Gantt\Entity;

use DateTime;
use Ksfraser\Gantt\Entity\GanttTask;
use PHPUnit\Framework\TestCase;

class GanttTaskTest extends TestCase
{
    public function testConstructorInitializesWithDefaults(): void
    {
        $task = new GanttTask('task-1', 'Test Task');

        $this->assertEquals('task-1', $task->getId());
        $this->assertEquals('Test Task', $task->getName());
        $this->assertNull($task->getStartDate());
        $this->assertNull($task->getEndDate());
        $this->assertEquals(0.0, $task->getProgress());
        $this->assertEquals('pending', $task->getStatus());
        $this->assertEquals('medium', $task->getPriority());
        $this->assertEquals('', $task->getAssignee());
        $this->assertEquals([], $task->getDependencies());
        $this->assertNull($task->getParentId());
        $this->assertEquals('#3b82f6', $task->getColor());
        $this->assertFalse($task->isMileStone());
    }

    public function testConstructorAcceptsDateTimeObjects(): void
    {
        $startDate = new DateTime('2024-01-01');
        $endDate = new DateTime('2024-01-31');

        $task = new GanttTask('task-2', 'Date Task', $startDate, $endDate);

        $this->assertEquals($startDate, $task->getStartDate());
        $this->assertEquals($endDate, $task->getEndDate());
    }

    public function testSetNameUpdatesName(): void
    {
        $task = new GanttTask('task-1', 'Original');
        $result = $task->setName('Updated');

        $this->assertEquals('Updated', $task->getName());
        $this->assertSame($task, $result);
    }

    public function testSetProgressClampsValueBetweenZeroAndOneHundred(): void
    {
        $task = new GanttTask('task-1', 'Test');

        $task->setProgress(-50);
        $this->assertEquals(0.0, $task->getProgress());

        $task->setProgress(150);
        $this->assertEquals(100.0, $task->getProgress());

        $task->setProgress(50);
        $this->assertEquals(50.0, $task->getProgress());
    }

    public function testAddDependencyDoesNotAddDuplicates(): void
    {
        $task = new GanttTask('task-1', 'Test');

        $task->addDependency('dep-1');
        $task->addDependency('dep-2');
        $task->addDependency('dep-1');

        $this->assertCount(2, $task->getDependencies());
        $this->assertContains('dep-1', $task->getDependencies());
        $this->assertContains('dep-2', $task->getDependencies());
    }

    public function testRemoveDependencyRemovesExistingDependency(): void
    {
        $task = new GanttTask('task-1', 'Test');
        $task->addDependency('dep-1');
        $task->addDependency('dep-2');

        $task->removeDependency('dep-1');

        $this->assertCount(1, $task->getDependencies());
        $this->assertNotContains('dep-1', $task->getDependencies());
        $this->assertContains('dep-2', $task->getDependencies());
    }

    public function testGetDurationReturnsCorrectNumberOfDays(): void
    {
        $startDate = new DateTime('2024-01-01');
        $endDate = new DateTime('2024-01-15');

        $task = new GanttTask('task-1', 'Test', $startDate, $endDate);

        $this->assertEquals(14, $task->getDuration());
    }

    public function testGetDurationReturnsNullWhenDatesAreMissing(): void
    {
        $task = new GanttTask('task-1', 'No Dates');
        $this->assertNull($task->getDuration());

        $task->setStartDate(new DateTime('2024-01-01'));
        $this->assertNull($task->getDuration());
    }

    public function testIsOverdueReturnsTrueForPastDueIncompleteTasks(): void
    {
        $yesterday = (new DateTime())->modify('-1 day');
        $task = new GanttTask('task-1', 'Overdue', $yesterday, $yesterday);
        $task->setProgress(50);

        $this->assertTrue($task->isOverdue());
    }

    public function testIsOverdueReturnsFalseForCompletedTasks(): void
    {
        $yesterday = (new DateTime())->modify('-1 day');
        $task = new GanttTask('task-1', 'Completed', $yesterday, $yesterday);
        $task->setProgress(100);

        $this->assertFalse($task->isOverdue());
    }

    public function testIsCompletedReturnsTrueWhenProgressIsOneHundred(): void
    {
        $task = new GanttTask('task-1', 'Complete');
        $task->setProgress(100);

        $this->assertTrue($task->isCompleted());
    }

    public function testIsCompletedReturnsTrueWhenStatusIsCompleted(): void
    {
        $task = new GanttTask('task-1', 'Complete');
        $task->setStatus('completed');

        $this->assertTrue($task->isCompleted());
    }

    public function testToArrayReturnsCorrectStructure(): void
    {
        $task = new GanttTask('task-1', 'Test Task');
        $task->setProgress(50);
        $task->setStatus('in_progress');

        $array = $task->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('start_date', $array);
        $this->assertArrayHasKey('end_date', $array);
        $this->assertArrayHasKey('progress', $array);
        $this->assertArrayHasKey('status', $array);
        $this->assertArrayHasKey('priority', $array);
        $this->assertArrayHasKey('assignee', $array);
        $this->assertArrayHasKey('dependencies', $array);
        $this->assertArrayHasKey('parent_id', $array);
        $this->assertArrayHasKey('color', $array);
        $this->assertArrayHasKey('is_milestone', $array);
        $this->assertArrayHasKey('duration', $array);
    }

    public function testFromArrayCreatesTaskWithAllProperties(): void
    {
        $data = [
            'id' => 'task-from-array',
            'name' => 'From Array Task',
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-15',
            'progress' => 75,
            'status' => 'completed',
            'priority' => 'high',
            'assignee' => 'john@example.com',
            'dependencies' => ['dep-1', 'dep-2'],
            'parent_id' => 'parent-task',
            'color' => '#ff0000',
            'is_milestone' => true,
        ];

        $task = GanttTask::fromArray($data);

        $this->assertEquals('task-from-array', $task->getId());
        $this->assertEquals('From Array Task', $task->getName());
        $this->assertEquals(75.0, $task->getProgress());
        $this->assertEquals('completed', $task->getStatus());
        $this->assertEquals('high', $task->getPriority());
        $this->assertEquals('john@example.com', $task->getAssignee());
        $this->assertCount(2, $task->getDependencies());
        $this->assertEquals('parent-task', $task->getParentId());
        $this->assertEquals('#ff0000', $task->getColor());
        $this->assertTrue($task->isMileStone());
    }

    public function testFromArrayHandlesMissingOptionalFields(): void
    {
        $data = [
            'id' => 'minimal-task',
            'name' => 'Minimal Task',
        ];

        $task = GanttTask::fromArray($data);

        $this->assertEquals('minimal-task', $task->getId());
        $this->assertEquals('Minimal Task', $task->getName());
        $this->assertEquals(0.0, $task->getProgress());
        $this->assertEquals('pending', $task->getStatus());
        $this->assertEquals([], $task->getDependencies());
    }

    public function testSetMileStoneUpdatesMilestoneFlag(): void
    {
        $task = new GanttTask('task-1', 'Test');

        $this->assertFalse($task->isMileStone());

        $task->setMileStone(true);
        $this->assertTrue($task->isMileStone());

        $task->setMileStone(false);
        $this->assertFalse($task->isMileStone());
    }

    public function testFluentSettersReturnSelf(): void
    {
        $task = new GanttTask('task-1', 'Test');

        $result = $task
            ->setName('Name')
            ->setStatus('active')
            ->setPriority('high')
            ->setAssignee('user')
            ->setColor('#ff0000');

        $this->assertSame($task, $result);
    }
}