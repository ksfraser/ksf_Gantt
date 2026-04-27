# ksf_Gantt - PHP Gantt Chart Library

A reusable PHP library for creating, rendering, and managing Gantt charts with resource utilization tracking.

## Features

- **GanttTask Entity** - Tasks with dates, progress, dependencies, priorities, assignees
- **GanttChart Entity** - Collections of tasks with hierarchy and cycle detection
- **HTML Renderer** - Beautiful CSS-based Gantt chart visualization
- **SVG Renderer** - Scalable vector graphics export
- **Resource Utilization** - Track allocation, capacity planning, overload detection
- **FullCalendar Integration** - Export to FullCalendar event format
- **JSON Export** - Full data serialization

## Installation

```bash
composer require ksfraser/ksf-gantt
```

## Quick Start

```php
use Ksfraser\Gantt\Entity\GanttTask;
use Ksfraser\Gantt\Entity\GanttChart;
use Ksfraser\Gantt\Service\GanttRenderer;

$chart = new GanttChart('project-1', 'My Project');

$task1 = new GanttTask('T1', 'Planning', new DateTime('2025-01-01'), new DateTime('2025-01-05'));
$task1->setProgress(100);

$task2 = new GanttTask('T2', 'Development', new DateTime('2025-01-06'), new DateTime('2025-01-20'));
$task2->setAssignee('John Doe');
$task2->setEstimatedHours(80);
$task2->setProgress(50);
$task2->addDependency('T1');

$task3 = new GanttTask('T3', 'Testing', new DateTime('2025-01-21'), new DateTime('2025-01-25'));
$task3->addDependency('T2');

$milestone = new GanttTask('M1', 'Launch!');
$milestone->setMileStone(true);
$milestone->setStartDate(new DateTime('2025-01-26'));

$chart->addTask($task1)->addTask($task2)->addTask($task3)->addTask($milestone);

$renderer = new GanttRenderer();
echo $renderer->renderHtml($chart);
```

## Entities

### GanttTask

| Property | Type | Description |
|----------|------|------------|
| `id` | string | Unique task identifier |
| `name` | string | Task name |
| `startDate` | ?DateTime | Start date |
| `endDate` | ?DateTime | End date |
| `progress` | float | Progress percentage (0-100) |
| `status` | string | pending/in_progress/completed |
| `priority` | string | low/medium/high/critical |
| `assignee` | string | Assigned user |
| `dependencies` | array | Array of task IDs |
| `parentId` | ?string | Parent task for hierarchy |
| `color` | string | Hex color code |
| `isMileStone` | bool | Is milestone marker |

### GanttChart

| Method | Description |
|--------|------------|
| `addTask()` | Add task to chart |
| `getTasks()` | Get all tasks |
| `getTasksByAssignee()` | Filter by assignee |
| `getRootTasks()` | Get tasks without parent |
| `getSubtasks()` | Get child tasks |
| `hasDependencyCycle()` | Detect cycles |
| `topologicalSort()` | Sort by dependencies |

## Rendering

### HTML (default - CSS-based)

```php
$renderer = new GanttRenderer([
    'dayWidth' => 40,      // pixels per day
    'rowHeight' => 40,     // pixels per row
    'headerHeight' => 50,
    'sidebarWidth' => 250,
]);

echo $renderer->renderHtml($chart);
```

### SVG

```php
echo $renderer->renderSvg($chart);
```

### JSON

```php
echo $renderer->toJson($chart);
```

### FullCalendar Format

```php
$events = $renderer->toFullCalendar($chart);
// Returns array for FullCalendar.js
```

## Resource Utilization

```php
use Ksfraser\Gantt\Service\ResourceUtilization;

$utilService = new ResourceUtilization([
    'maxDailyHours' => 8.0,
    'warningThreshold' => 0.8,
    'overloadThreshold' => 1.0,
]);

// Get utilization by assignee
$utilization = $utilService->calculateUtilization($chart);

// Get overloaded resources
$overloaded = $utilService->getOverloadedResources($chart);

// Get daily capacity
$capacity = $utilService->getCapacityPlanning($chart);

// Export to CSV
echo $utilService->exportToCsv($chart);
```

## Integration with ksf_FA_ProjectManagement

```php
use Ksfraser\Gantt\Entity\GanttTask;
use Ksfraser\Gantt\Entity\GanttChart;

// Convert FA PM tasks to Gantt
$chart = new GanttChart($project->getProjectId(), $project->getName());

$result = get_pm_tasks($project->getProjectId());
while ($row = db_fetch_assoc($result)) {
    $task = new GanttTask(
        $row['task_id'],
        $row['name'],
        $row['start_date'] ? new DateTime($row['start_date']) : null,
        $row['end_date'] ? new DateTime($row['end_date']) : null
    );
    $task->setProgress((float) $row['progress']);
    $task->setStatus($row['status']);
    $task->setAssignee($row['assigned_to']);
    $task->setEstimatedHours((float) $row['estimated_hours']);
    $chart->addTask($task);
}

$renderer = new GanttRenderer();
echo $renderer->renderHtml($chart);
```

## Requirements

- PHP 8.1+

## License

MIT