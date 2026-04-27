<?php
/**
 * GanttChart Entity
 *
 * Represents a collection of tasks forming a Gantt chart
 *
 * @package Ksfraser\Gantt\Entity
 */

declare(strict_types=1);

namespace Ksfraser\Gantt\Entity;

use DateTime;

class GanttChart
{
    private string $id;
    private string $name;
    private ?DateTime $startDate;
    private ?DateTime $endDate;
    private array $tasks;
    private string $timezone;

    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
        $this->tasks = [];
        $this->timezone = 'UTC';
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getStartDate(): ?DateTime
    {
        return $this->startDate;
    }

    public function getEndDate(): ?DateTime
    {
        return $this->endDate;
    }

    public function getTasks(): array
    {
        return $this->tasks;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }

    public function addTask(GanttTask $task): self
    {
        $this->tasks[$task->getId()] = $task;
        $this->recalculateDateRange();
        return $this;
    }

    public function removeTask(string $taskId): self
    {
        unset($this->tasks[$taskId]);
        foreach ($this->tasks as $task) {
            $task->removeDependency($taskId);
        }
        $this->recalculateDateRange();
        return $this;
    }

    public function getTask(string $taskId): ?GanttTask
    {
        return $this->tasks[$taskId] ?? null;
    }

    public function hasTask(string $taskId): bool
    {
        return isset($this->tasks[$taskId]);
    }

    public function getTaskCount(): int
    {
        return count($this->tasks);
    }

    public function getCompletedCount(): int
    {
        return count(array_filter($this->tasks, fn($t) => $t->isCompleted()));
    }

    public function getOverallProgress(): float
    {
        if (empty($this->tasks)) {
            return 0.0;
        }
        $total = array_reduce($this->tasks, fn($sum, $t) => $sum + $t->getProgress(), 0.0);
        return $total / count($this->tasks);
    }

    public function getTasksByStatus(string $status): array
    {
        return array_filter($this->tasks, fn($t) => $t->getStatus() === $status);
    }

    public function getTasksByAssignee(string $assignee): array
    {
        return array_filter($this->tasks, fn($t) => $t->getAssignee() === $assignee);
    }

    public function getRootTasks(): array
    {
        return array_filter($this->tasks, fn($t) => $t->getParentId() === null);
    }

    public function getSubtasks(string $parentId): array
    {
        return array_filter($this->tasks, fn($t) => $t->getParentId() === $parentId);
    }

    public function getAssignees(): array
    {
        $assignees = [];
        foreach ($this->tasks as $task) {
            if ($task->getAssignee()) {
                $assignees[$task->getAssignee()] = true;
            }
        }
        return array_keys($assignees);
    }

    public function hasDependencyCycle(): bool
    {
        $visited = [];
        $recursionStack = [];

        $dfs = function(string $taskId) use (&$visited, &$recursionStack, &$dfs): bool {
            if (isset($recursionStack[$taskId])) {
                return true;
            }
            if (isset($visited[$taskId])) {
                return false;
            }

            $visited[$taskId] = true;
            $recursionStack[$taskId] = true;

            $task = $this->getTask($taskId);
            if ($task) {
                foreach ($task->getDependencies() as $depId) {
                    if ($dfs($depId)) {
                        return true;
                    }
                }
            }

            unset($recursionStack[$taskId]);
            return false;
        };

        foreach ($this->tasks as $taskId => $task) {
            if ($dfs($taskId)) {
                return true;
            }
        }

        return false;
    }

    public function topologicalSort(): array
    {
        $sorted = [];
        $visited = [];
        $temp = [];

        $visit = function(string $taskId) use (&$sorted, &$visited, &$temp, &$visit): void {
            if (isset($temp[$taskId])) {
                throw new \RuntimeException("Circular dependency detected at task: $taskId");
            }
            if (isset($visited[$taskId])) {
                return;
            }

            $temp[$taskId] = true;

            $task = $this->getTask($taskId);
            if ($task) {
                foreach ($task->getDependencies() as $depId) {
                    $visit($depId);
                }
            }

            unset($temp[$taskId]);
            $visited[$taskId] = true;
            $sorted[] = $taskId;
        };

        foreach ($this->tasks as $taskId => $task) {
            $visit($taskId);
        }

        return $sorted;
    }

    private function recalculateDateRange(): void
    {
        $startDates = [];
        $endDates = [];

        foreach ($this->tasks as $task) {
            if ($task->getStartDate()) {
                $startDates[] = $task->getStartDate();
            }
            if ($task->getEndDate()) {
                $endDates[] = $task->getEndDate();
            }
        }

        $this->startDate = !empty($startDates) ? min($startDates) : null;
        $this->endDate = !empty($endDates) ? max($endDates) : null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'start_date' => $this->startDate?->format(\DateTimeInterface::ATOM),
            'end_date' => $this->endDate?->format(\DateTimeInterface::ATOM),
            'timezone' => $this->timezone,
            'tasks' => array_map(fn($t) => $t->toArray(), $this->tasks),
            'task_count' => $this->getTaskCount(),
            'completed_count' => $this->getCompletedCount(),
            'overall_progress' => $this->getOverallProgress(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}