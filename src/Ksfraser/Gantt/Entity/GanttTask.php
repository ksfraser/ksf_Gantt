<?php
/**
 * GanttTask Entity
 *
 * Represents a task in a Gantt chart with dates, progress, dependencies
 *
 * @package Ksfraser\Gantt\Entity
 */

declare(strict_types=1);

namespace Ksfraser\Gantt\Entity;

use DateTime;
use DateTimeInterface;

class GanttTask
{
    private string $id;
    private string $name;
    private ?DateTime $startDate;
    private ?DateTime $endDate;
    private float $progress;
    private string $status;
    private string $priority;
    private string $assignee;
    private array $dependencies;
    private ?string $parentId;
    private string $color;
    private bool $isMileStone;

    public function __construct(
        string $id,
        string $name,
        ?DateTime $startDate = null,
        ?DateTime $endDate = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->progress = 0.0;
        $this->status = 'pending';
        $this->priority = 'medium';
        $this->assignee = '';
        $this->dependencies = [];
        $this->parentId = null;
        $this->color = '#3b82f6';
        $this->isMileStone = false;
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

    public function setStartDate(?DateTime $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTime $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getProgress(): float
    {
        return $this->progress;
    }

    public function setProgress(float $progress): self
    {
        $this->progress = max(0.0, min(100.0, $progress));
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function getAssignee(): string
    {
        return $this->assignee;
    }

    public function setAssignee(string $assignee): self
    {
        $this->assignee = $assignee;
        return $this;
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function addDependency(string $taskId): self
    {
        if (!in_array($taskId, $this->dependencies)) {
            $this->dependencies[] = $taskId;
        }
        return $this;
    }

    public function removeDependency(string $taskId): self
    {
        $this->dependencies = array_filter($this->dependencies, fn($id) => $id !== $taskId);
        return $this;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function setParentId(?string $parentId): self
    {
        $this->parentId = $parentId;
        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;
        return $this;
    }

    public function isMileStone(): bool
    {
        return $this->isMileStone;
    }

    public function setMileStone(bool $isMileStone): self
    {
        $this->isMileStone = $isMileStone;
        return $this;
    }

    public function getDuration(): ?int
    {
        if ($this->startDate === null || $this->endDate === null) {
            return null;
        }
        return (int) $this->startDate->diff($this->endDate)->days;
    }

    public function isOverdue(): bool
    {
        if ($this->endDate === null) {
            return false;
        }
        return $this->endDate < new DateTime() && $this->progress < 100.0;
    }

    public function isCompleted(): bool
    {
        return $this->progress >= 100.0 || $this->status === 'completed';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'start_date' => $this->startDate?->format(DateTimeInterface::ATOM),
            'end_date' => $this->endDate?->format(DateTimeInterface::ATOM),
            'progress' => $this->progress,
            'status' => $this->status,
            'priority' => $this->priority,
            'assignee' => $this->assignee,
            'dependencies' => $this->dependencies,
            'parent_id' => $this->parentId,
            'color' => $this->color,
            'is_milestone' => $this->isMileStone,
            'duration' => $this->getDuration(),
        ];
    }

    public static function fromArray(array $data): self
    {
        $task = new self(
            $data['id'],
            $data['name'],
            isset($data['start_date']) ? new DateTime($data['start_date']) : null,
            isset($data['end_date']) ? new DateTime($data['end_date']) : null
        );

        if (isset($data['progress'])) {
            $task->setProgress((float) $data['progress']);
        }
        if (isset($data['status'])) {
            $task->setStatus($data['status']);
        }
        if (isset($data['priority'])) {
            $task->setPriority($data['priority']);
        }
        if (isset($data['assignee'])) {
            $task->setAssignee($data['assignee']);
        }
        if (isset($data['dependencies'])) {
            foreach ($data['dependencies'] as $depId) {
                $task->addDependency($depId);
            }
        }
        if (isset($data['parent_id'])) {
            $task->setParentId($data['parent_id']);
        }
        if (isset($data['color'])) {
            $task->setColor($data['color']);
        }
        if (isset($data['is_milestone'])) {
            $task->setMileStone((bool) $data['is_milestone']);
        }

        return $task;
    }
}