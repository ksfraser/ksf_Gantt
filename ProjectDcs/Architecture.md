# Architecture - ksf_Gantt

## Document Information
- **Module**: ksf_Gantt
- **Version**: 1.0.0
- **Date**: 2026-05-13
- **Status**: Implemented
- **Author**: KSFII Development Team

---

## 1. Technical Architecture

### 1.1 High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     ksf_Gantt Module                        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────────┐    ┌─────────────────────────────────┐ │
│  │     Entities     │    │           Services              │ │
│  ├─────────────────┤    ├─────────────────────────────────┤ │
│  │ GanttChart      │◄──►│ GanttRenderer                   │ │
│  │ GanttTask       │    │ - renderHtml()                  │ │
│  │                 │    │ - renderSvg()                   │ │
│  │                 │    │ - toJson()                      │ │
│  │                 │    │ - toFullCalendar()             │ │
│  │                 │    ├─────────────────────────────────┤ │
│  │                 │    │ ResourceUtilization             │ │
│  │                 │    │ - calculateUtilization()        │ │
│  │                 │    │ - getOverloadedResources()     │ │
│  │                 │    │ - getCapacityPlanning()         │ │
│  └─────────────────┘    └─────────────────────────────────┘ │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### 1.2 Class Diagram

```
┌────────────────────────────────────────────────────────────────┐
│                          GanttChart                            │
├────────────────────────────────────────────────────────────────┤
│ - id: string                                                   │
│ - name: string                                                  │
│ - startDate: ?DateTime                                          │
│ - endDate: ?DateTime                                            │
│ - tasks: array                                                  │
│ - timezone: string                                              │
├────────────────────────────────────────────────────────────────┤
│ + addTask(GanttTask): self                                      │
│ + removeTask(string): self                                      │
│ + getTask(string): ?GanttTask                                   │
│ + hasTask(string): bool                                         │
│ + getTaskCount(): int                                           │
│ + getCompletedCount(): int                                      │
│ + getOverallProgress(): float                                   │
│ + getTasksByStatus(string): array                               │
│ + getTasksByAssignee(string): array                             │
│ + getRootTasks(): array                                         │
│ + getSubtasks(string): array                                    │
│ + getAssignees(): array                                         │
│ + hasDependencyCycle(): bool                                   │
│ + topologicalSort(): array                                      │
│ + toArray(): array                                              │
│ + toJson(): string                                              │
└────────────────────────────────────────────────────────────────┘
                              ▲
                              │ contains
                              │
                    ┌─────────┴─────────┐
                    │                   │
                    │   1..*            │
┌────────────────────────────────────────────────────────────────┐
│                          GanttTask                             │
├────────────────────────────────────────────────────────────────┤
│ - id: string                                                   │
│ - name: string                                                  │
│ - startDate: ?DateTime                                         │
│ - endDate: ?DateTime                                            │
│ - progress: float (0-100)                                       │
│ - status: string                                                │
│ - priority: string                                              │
│ - assignee: string                                             │
│ - dependencies: array                                           │
│ - parentId: ?string                                             │
│ - color: string                                                │
│ - isMileStone: bool                                             │
│ - estimatedHours: float                                        │
│ - actualHours: float                                           │
├────────────────────────────────────────────────────────────────┤
│ + setProgress(float): self                                      │
│ + setStatus(string): self                                      │
│ + setAssignee(string): self                                     │
│ + addDependency(string): self                                   │
│ + removeDependency(string): self                                 │
│ + getDuration(): ?int                                          │
│ + isOverdue(): bool                                            │
│ + isCompleted(): bool                                          │
│ + toArray(): array                                              │
│ + fromArray(array): self (static)                              │
└────────────────────────────────────────────────────────────────┘


┌────────────────────────────────────────────────────────────────┐
│                       GanttRenderer                            │
├────────────────────────────────────────────────────────────────┤
│ - dayWidth: int = 40                                           │
│ - rowHeight: int = 40                                          │
│ - headerHeight: int = 50                                       │
│ - sidebarWidth: int = 250                                      │
│ - primaryColor: string = "#3b82f6"                             │
│ - completedColor: string = "#22c55e"                           │
│ - overdueColor: string = "#ef4444"                             │
│ - inProgressColor: string = "#f59e0b"                          │
├────────────────────────────────────────────────────────────────┤
│ + renderHtml(GanttChart, array): string                         │
│ + renderSvg(GanttChart): string                                 │
│ + toJson(GanttChart): string                                   │
│ + toFullCalendar(GanttChart): array                            │
└────────────────────────────────────────────────────────────────┘


┌────────────────────────────────────────────────────────────────┐
│                     ResourceUtilization                        │
├────────────────────────────────────────────────────────────────┤
│ - resources: array                                              │
│ - maxDailyHours: float = 8.0                                   │
│ - warningThreshold: float = 0.8                                │
│ - overloadThreshold: float = 1.0                                │
├────────────────────────────────────────────────────────────────┤
│ + calculateUtilization(GanttChart, ?DateTime, ?DateTime): array│
│ + getDailyUtilization(GanttChart, string, DateTime): float       │
│ + getCapacityPlanning(GanttChart, ?DateTime, ?DateTime): array   │
│ + getOverloadedResources(GanttChart): array                     │
│ + getUnderutilizedResources(GanttChart): array                   │
│ + getResourceWorkload(GanttChart): array                        │
│ + exportToCsv(GanttChart): string                               │
│ + toJson(GanttChart): string                                    │
└────────────────────────────────────────────────────────────────┘
```

---

## 2. Data Flow Diagrams

### 2.1 Task Creation Flow

```
┌──────────┐    ┌──────────────┐    ┌────────────┐    ┌──────────┐
│  User    │    │   Renderer   │    │ GanttChart │    │ GanttTask│
└──────────┘    └──────────────┘    └────────────┘    └──────────┘
     │                 │                 │                │
     │ Create task    │                 │                │
     │────────────────>│                 │                │
     │                 │                 │                │
     │                 │ new GanttTask() │                │
     │                 │────────────────>│                │
     │                 │                 │                │
     │                 │                 │ addTask()      │
     │                 │                 │────────────────>│
     │                 │                 │                │
     │                 │                 │ recalculate   │
     │                 │                 │ dates          │
     │                 │                 │                │
     │ Response        │<────────────────│                │
     │<────────────────│                 │                │
```

### 2.2 Dependency Cycle Detection

```
┌──────────────┐    ┌──────────────┐    ┌────────────┐
│  Add Dep    │    │  GanttChart  │    │   DFS      │
│             │    │              │    │  Traversal │
└──────────────┘    └──────────────┘    └────────────┘
      │                 │                 │
      │ addDependency() │                 │
      │────────────────>│                 │
      │                 │                 │
      │                 │ hasDependencyCycle()
      │                 │────────────────>│
      │                 │                 │
      │                 │ Visit each task │
      │                 │ Track visited   │
      │                 │ Track recursion │
      │                 │                 │
      │                 │<────────────────│
      │                 │  Result: bool   │
      │                 │                 │
      │ Result          │                 │
      │<────────────────│                 │
```

---

## 3. Database Schema (Platform-Provided)

### 3.1 Gantt Charts Table

```sql
CREATE TABLE `{PREFIX}gantt_charts` (
    `id` VARCHAR(32) PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `start_date` DATE,
    `end_date` DATE,
    `timezone` VARCHAR(64) DEFAULT 'UTC',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP
);
```

### 3.2 Tasks Table

```sql
CREATE TABLE `{PREFIX}gantt_tasks` (
    `id` VARCHAR(32) PRIMARY KEY,
    `chart_id` VARCHAR(32) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `start_date` DATE,
    `end_date` DATE,
    `progress` DECIMAL(5,2) DEFAULT 0,
    `status` ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    `priority` ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    `assignee` VARCHAR(128),
    `parent_id` VARCHAR(32),
    `color` VARCHAR(7),
    `is_milestone` TINYINT(1) DEFAULT 0,
    `estimated_hours` DECIMAL(10,2) DEFAULT 0,
    `actual_hours` DECIMAL(10,2) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`chart_id`) REFERENCES `{PREFIX}gantt_charts`(`id`) ON DELETE CASCADE,
    INDEX `idx_chart_id` (`chart_id`),
    INDEX `idx_assignee` (`assignee`)
);
```

### 3.3 Dependencies Table

```sql
CREATE TABLE `{PREFIX}gantt_dependencies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `task_id` VARCHAR(32) NOT NULL,
    `depends_on` VARCHAR(32) NOT NULL,
    `type` ENUM('FS', 'SS', 'FF', 'SF') DEFAULT 'FS',
    FOREIGN KEY (`task_id`) REFERENCES `{PREFIX}gantt_tasks`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`depends_on`) REFERENCES `{PREFIX}gantt_tasks`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_task_dependency` (`task_id`, `depends_on`)
);
```

---

## 4. API Design

### 4.1 GanttChart API

```php
class GanttChart
{
    public function __construct(string $id, string $name);
    
    // Task Management
    public function addTask(GanttTask $task): self;
    public function removeTask(string $taskId): self;
    public function getTask(string $taskId): ?GanttTask;
    public function hasTask(string $taskId): bool;
    public function getTasks(): array;
    
    // Analytics
    public function getTaskCount(): int;
    public function getCompletedCount(): int;
    public function getOverallProgress(): float;
    public function getTasksByStatus(string $status): array;
    public function getTasksByAssignee(string $assignee): array;
    public function getRootTasks(): array;
    public function getSubtasks(string $parentId): array;
    public function getAssignees(): array;
    
    // Dependency Management
    public function hasDependencyCycle(): bool;
    public function topologicalSort(): array;
    
    // Serialization
    public function toArray(): array;
    public function toJson(): string;
}
```

### 4.2 GanttTask API

```php
class GanttTask
{
    public function __construct(
        string $id,
        string $name,
        ?DateTime $startDate = null,
        ?DateTime $endDate = null
    );
    
    // Properties
    public function setName(string $name): self;
    public function setStartDate(?DateTime $date): self;
    public function setEndDate(?DateTime $date): self;
    public function setProgress(float $progress): self;
    public function setStatus(string $status): self;
    public function setPriority(string $priority): self;
    public function setAssignee(string $assignee): self;
    
    // Dependencies
    public function addDependency(string $taskId): self;
    public function removeDependency(string $taskId): self;
    public function getDependencies(): array;
    
    // Hierarchy
    public function setParentId(?string $parentId): self;
    public function getParentId(): ?string;
    
    // Status
    public function getDuration(): ?int;
    public function isOverdue(): bool;
    public function isCompleted(): bool;
    
    // Serialization
    public function toArray(): array;
    public static function fromArray(array $data): self;
}
```

### 4.3 GanttRenderer API

```php
class GanttRenderer
{
    public function __construct(array $options = []);
    
    public function renderHtml(GanttChart $chart, array $options = []): string;
    public function renderSvg(GanttChart $chart): string;
    public function toJson(GanttChart $chart): string;
    public function toFullCalendar(GanttChart $chart): array;
}
```

### 4.4 ResourceUtilization API

```php
class ResourceUtilization
{
    public function __construct(array $options = []);
    
    public function calculateUtilization(
        GanttChart $chart,
        ?DateTime $startDate = null,
        ?DateTime $endDate = null
    ): array;
    
    public function getDailyUtilization(
        GanttChart $chart,
        string $assignee,
        DateTime $date
    ): float;
    
    public function getCapacityPlanning(
        GanttChart $chart,
        ?DateTime $startDate = null,
        ?DateTime $endDate = null
    ): array;
    
    public function getOverloadedResources(GanttChart $chart): array;
    public function getUnderutilizedResources(GanttChart $chart): array;
    public function getResourceWorkload(GanttChart $chart): array;
    public function exportToCsv(GanttChart $chart): string;
    public function toJson(GanttChart $chart): string;
}
```

---

## 5. Sequence Diagrams

### 5.1 Render Gantt Chart

```
Client         Renderer         GanttChart        GanttTask
  │                │                │                │
  │ renderHtml()   │                │                │
  │───────────────>│                │                │
  │                │                │                │
  │                │ getStartDate() │                │
  │                │───────────────>│                │
  │                │                │                │
  │                │<───────────────│                │
  │                │                │                │
  │                │ getTasks()     │                │
  │                │───────────────>│                │
  │                │                │                │
  │                │<───────────────│                │
  │                │                │                │
  │                │ For each task: │                │
  │                │ getStartDate() │                │
  │                │────────────────>│                │
  │                │ getProgress()  │                │
  │                │────────────────>│                │
  │                │                │                │
  │                │ Render HTML    │                │
  │ HTML           │                │                │
  │<───────────────│                │                │
```

---

## 6. Rendering Implementation

### 6.1 HTML Rendering Components

| Component | Description |
|-----------|-------------|
| Container | Main wrapper with overflow handling |
| Sidebar | Task names column |
| Timeline Header | Day/date headers |
| Grid | Background day cells |
| Task Bars | Colored task duration bars |
| Milestones | Diamond markers |
| Progress Fill | Completion overlay |

### 6.2 CSS Styling

```css
.gantt-container { overflow-x: auto; }
.gantt-sidebar { position: absolute; background: #f9fafb; }
.gantt-task-bar { border-radius: 4px; transition: transform 0.2s; }
.gantt-task-bar:hover { transform: scale(1.02); }
.gantt-milestone { transform: rotate(45deg); }
```

---

## 7. Error Handling

| Scenario | Handling |
|----------|----------|
| Missing dates | Use chart date range |
| Cycle detected | Throw RuntimeException |
| Invalid task ID | Return null from getTask() |
| Empty chart | Render empty state |

---

## 8. Performance Considerations

### 8.1 Optimizations
- Lazy date range calculation
- Task filtering instead of full iteration
- Cached date calculations
- Efficient SVG generation

### 8.2 Limits
- Recommended max tasks: 500
- Warning threshold: 1000 tasks
- Memory limit consideration for large charts

---

*Document Version: 1.0.0*
*Last Updated: 2026-05-13*