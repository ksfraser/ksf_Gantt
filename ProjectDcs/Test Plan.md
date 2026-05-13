# Test Plan - ksf_Gantt

## Document Information
- **Module**: ksf_Gantt
- **Version**: 1.0.0
- **Date**: 2026-05-13
- **Status**: Implemented
- **Author**: KSFII Development Team

---

## 1. Test Overview

### 1.1 Test Objectives
- Verify Gantt chart creation and management
- Validate task operations and properties
- Confirm dependency handling and cycle detection
- Ensure rendering produces correct output
- Validate resource utilization calculations

### 1.2 Scope

| Category | Included |
|----------|----------|
| Unit Tests | Yes |
| Integration Tests | Yes |
| Rendering Tests | Yes |

---

## 2. Test Cases

### 2.1 GanttChart Tests

#### CHART-001: Create Chart
**Test ID**: CHART-001
**Priority**: High

**Test Steps**:
1. new GanttChart("chart1", "Project 1")
2. Assert getId() === "chart1"
3. Assert getName() === "Project 1"
4. Assert getTimezone() === "UTC"
5. Assert getTasks() is empty

---

#### CHART-002: Date Range Calculation
**Test ID**: CHART-002
**Priority**: High

**Test Steps**:
1. Create chart
2. Add task with start=2026-05-01, end=2026-05-15
3. Add task with start=2026-05-10, end=2026-05-25
4. Assert chart.getStartDate() === 2026-05-01
5. Assert chart.getEndDate() === 2026-05-25

---

#### CHART-003: Chart Analytics
**Test ID**: CHART-003
**Priority**: Medium

**Test Steps**:
1. Create chart with 5 tasks
2. Set 2 tasks to 100% progress
3. Assert getTaskCount() === 5
4. Assert getCompletedCount() === 2
5. Assert getOverallProgress() === 40% average

---

#### CHART-004: Get Tasks By Status
**Test ID**: CHART-004
**Priority**: Medium

**Test Steps**:
1. Create chart with tasks in different statuses
2. Call getTasksByStatus("pending")
3. Assert only pending tasks returned
4. Call getTasksByStatus("in_progress")
5. Assert only in_progress tasks returned

---

### 2.2 GanttTask Tests

#### TASK-001: Create Task
**Test ID**: TASK-001
**Priority**: High

**Test Steps**:
1. new GanttTask("task1", "Design Phase", start, end)
2. Assert getId() === "task1"
3. Assert getName() === "Design Phase"
4. Assert getProgress() === 0.0
5. Assert getStatus() === "pending"
6. Assert getPriority() === "medium"

---

#### TASK-002: Task Properties
**Test ID**: TASK-002
**Priority**: High

**Test Steps**:
1. Create task
2. setAssignee("Alice")
3. setPriority("high")
4. setColor("#ff0000")
5. Assert all properties set correctly

---

#### TASK-003: Progress Tracking
**Test ID**: TASK-003
**Priority**: High

**Test Steps**:
1. Create task
2. setProgress(50)
3. Assert getProgress() === 50
4. Assert isCompleted() === false
5. setProgress(100)
6. Assert getProgress() === 100
7. Assert isCompleted() === true

---

#### TASK-004: Overdue Detection
**Test ID**: TASK-004
**Priority**: High

**Test Steps**:
1. Create task with endDate = yesterday
2. Assert isOverdue() === true (progress < 100)
3. Set progress = 100
4. Assert isOverdue() === false (completed)
5. Create task with future endDate
6. Assert isOverdue() === false

---

#### TASK-005: Duration Calculation
**Test ID**: TASK-005
**Priority**: Medium

**Test Steps**:
1. Create task start=2026-05-01, end=2026-05-10
2. Assert getDuration() === 9 days
3. Create task with no dates
4. Assert getDuration() === null

---

#### TASK-006: Task Hierarchy
**Test ID**: TASK-006
**Priority**: Medium

**Test Steps**:
1. Create parent task
2. Create child task
3. Set child parentId = parent.id
4. Assert getParentId() returns parent ID
5. Assert parent.getSubtasks() includes child

---

#### TASK-007: Milestones
**Test ID**: TASK-007
**Priority**: Medium

**Test Steps**:
1. Create task
2. setMileStone(true)
3. Assert isMileStone() === true
4. Assert getDuration() === null (or 0)

---

### 2.3 Dependency Tests

#### DEP-001: Add Dependencies
**Test ID**: DEP-001
**Priority**: High

**Test Steps**:
1. Create tasks A, B
2. B.addDependency("A")
3. Assert B.getDependencies() includes "A"
4. Add duplicate dependency
5. Assert no duplicate in array

---

#### DEP-002: Cycle Detection - No Cycle
**Test ID**: DEP-002
**Priority**: High

**Test Steps**:
1. Create tasks A, B, C
2. B.addDependency("A")
3. C.addDependency("B")
4. Add to chart
5. Assert hasDependencyCycle() === false

---

#### DEP-003: Cycle Detection - With Cycle
**Test ID**: DEP-003
**Priority**: High

**Test Steps**:
1. Create tasks A, B, C
2. B.addDependency("A")
3. C.addDependency("B")
4. A.addDependency("C") // Creates cycle!
5. Add to chart
6. Assert hasDependencyCycle() === true

---

#### DEP-004: Topological Sort
**Test ID**: DEP-004
**Priority**: Medium

**Test Steps**:
1. Create tasks A, B, C with dependencies: C→B→A
2. Sort topologically
3. Assert A appears before B
4. Assert B appears before C
5. Assert all 3 tasks in result

---

### 2.4 Renderer Tests

#### RENDER-001: HTML Rendering
**Test ID**: RENDER-001
**Priority**: High

**Test Steps**:
1. Create chart with tasks
2. Call renderHtml(chart)
3. Assert output contains "gantt-container"
4. Assert output contains task names
5. Assert output contains CSS styles
6. Assert timeline header rendered

---

#### RENDER-002: SVG Rendering
**Test ID**: RENDER-002
**Priority**: Medium

**Test Steps**:
1. Create chart with tasks
2. Call renderSvg(chart)
3. Assert output starts with `<svg`
4. Assert output contains task rects
5. Assert contains correct width/height

---

#### RENDER-003: JSON Export
**Test ID**: RENDER-003
**Priority**: High

**Test Steps**:
1. Create chart with tasks
2. Call toJson(chart)
3. Assert valid JSON (json_decode)
4. Assert contains id, name, tasks
5. Assert dates in ATOM format

---

#### RENDER-004: FullCalendar Export
**Test ID**: RENDER-004
**Priority**: Medium

**Test Steps**:
1. Create chart with task
2. Call toFullCalendar(chart)
3. Assert returns array
4. Assert event has id, title, start, end
5. Assert color based on status

---

#### RENDER-005: Color Coding
**Test ID**: RENDER-005
**Priority**: Medium

**Test Steps**:
1. Create tasks with different statuses
2. Render HTML
3. Assert pending = blue class
4. Assert in_progress = amber class
5. Assert completed = green class
6. Assert overdue = red class

---

### 2.5 Resource Utilization Tests

#### RES-001: Calculate Utilization
**Test ID**: RES-001
**Priority**: High

**Test Steps**:
1. Create chart with assigned tasks
2. Call calculateUtilization(chart)
3. Assert returns array with assignee keys
4. Assert each has total_hours, available_hours
5. Assert utilization percentage calculated

---

#### RES-002: Daily Utilization
**Test ID**: RES-002
**Priority**: Medium

**Test Steps**:
1. Create task spanning 5 days, 40 hours
2. Call getDailyUtilization(chart, assignee, date)
3. Assert returns 1.0 (8 hours / 8 hours max)

---

#### RES-003: Overloaded Resources
**Test ID**: RES-003
**Priority**: High

**Test Steps**:
1. Create tasks exceeding 8h/day for resource
2. Call getOverloadedResources(chart)
3. Assert resource in results
4. Assert utilization > 100%

---

#### RES-004: Underutilized Resources
**Test ID**: RES-004
**Priority**: Medium

**Test Steps**:
1. Create task for resource
2. Call getUnderutilizedResources(chart)
3. Assert resource in results (if < 80%)

---

#### RES-005: Capacity Planning
**Test ID**: RES-005
**Priority**: Medium

**Test Steps**:
1. Create chart with tasks
2. Call getCapacityPlanning(chart, startDate, endDate)
3. Assert returns array keyed by date
4. Assert each date has total_hours
5. Assert each date has resources array

---

#### RES-006: CSV Export
**Test ID**: RES-006
**Priority**: Low

**Test Steps**:
1. Create chart with tasks
2. Call exportToCsv(chart)
3. Assert valid CSV
4. Assert contains header row
5. Assert contains assignee, hours, utilization

---

### 2.6 Edge Cases

#### EDGE-001: Empty Chart
**Test ID**: EDGE-001
**Priority**: Medium

**Test Steps**:
1. Create chart with no tasks
2. Assert getTaskCount() === 0
3. Assert getOverallProgress() === 0
4. Render HTML - should show empty state

---

#### EDGE-002: Task Not Found
**Test ID**: EDGE-002
**Priority**: Low

**Test Steps**:
1. Create chart
2. Call getTask("nonexistent")
3. Assert returns null
4. Assert hasTask("nonexistent") === false

---

#### EDGE-003: Progress Clamping
**Test ID**: EDGE-003
**Priority**: Low

**Test Steps**:
1. Create task
2. setProgress(150)
3. Assert progress === 100 (clamped)
4. setProgress(-10)
5. Assert progress === 0 (clamped)

---

## 3. Test Data

### 3.1 Sample Tasks

```php
$tasks = [
    [
        'id' => 'task1',
        'name' => 'Design Phase',
        'start' => '2026-05-01',
        'end' => '2026-05-10',
        'assignee' => 'Alice',
        'status' => 'completed',
        'progress' => 100,
    ],
    [
        'id' => 'task2',
        'name' => 'Development',
        'start' => '2026-05-10',
        'end' => '2026-05-25',
        'assignee' => 'Bob',
        'status' => 'in_progress',
        'progress' => 50,
    ],
    [
        'id' => 'task3',
        'name' => 'Testing',
        'start' => '2026-05-25',
        'end' => '2026-05-30',
        'assignee' => 'Charlie',
        'status' => 'pending',
        'progress' => 0,
    ],
];
```

### 3.2 Dependency Test Data

```php
// Valid dependency chain
$dependencies = [
    'task3' => ['task2'],
    'task2' => ['task1'],
    // task1 has no dependencies
];

// Invalid (cycle)
$cycleDependencies = [
    'task2' => ['task1'],
    'task1' => ['task3'],
    'task3' => ['task2'], // CYCLE!
];
```

---

## 4. Test Environment

### 4.1 Requirements
- PHP 7.3+
- PHPUnit 9.x
- DateTime extension

### 4.2 Configuration
```xml
<phpunit bootstrap="vendor/autoload.php" colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

---

## 5. Pass Criteria

| Category | Total | Passed | Coverage |
|----------|-------|--------|----------|
| GanttChart | 4 | - | 85% |
| GanttTask | 7 | - | 90% |
| Dependencies | 4 | - | 95% |
| Renderer | 5 | - | 80% |
| Resource | 6 | - | 75% |
| Edge Cases | 3 | - | N/A |
| **Total** | **29** | **-** | **84%** |

---

*Document Version: 1.0.0*
*Last Updated: 2026-05-13*