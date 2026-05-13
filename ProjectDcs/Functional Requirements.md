# Functional Requirements - ksf_Gantt

## Document Information
- **Module**: ksf_Gantt
- **Version**: 1.0.0
- **Date**: 2026-05-13
- **Status**: Implemented
- **Author**: KSFII Development Team

---

## 1. Overview

This document details functional requirements for the ksf_Gantt module, covering chart management, task operations, dependencies, and rendering.

---

## 2. Chart Management

### FR-CHART-001: Create Gantt Chart
**Priority**: High
**Description**: System shall create new Gantt chart with ID and name.

**Acceptance Criteria**:
- [ ] Unique string ID required
- [ ] Name is required, max 255 chars
- [ ] Timezone defaults to UTC
- [ ] Empty task collection on creation

---

### FR-CHART-002: Chart Date Range
**Priority**: High
**Description**: System shall calculate chart date range from tasks.

**Acceptance Criteria**:
- [ ] startDate = earliest task start date
- [ ] endDate = latest task end date
- [ ] Recalculated on task add/remove
- [ ] Null if no tasks with dates

---

### FR-CHART-003: Chart Analytics
**Priority**: Medium
**Description**: System shall provide chart-level metrics.

**Acceptance Criteria**:
- [ ] getTaskCount() returns total tasks
- [ ] getCompletedCount() returns 100% tasks
- [ ] getOverallProgress() returns average progress
- [ ] getAssignees() returns unique assignee list

---

## 3. Task Management

### FR-TASK-001: Create Task
**Priority**: High
**Description**: System shall create tasks with required properties.

**Acceptance Criteria**:
- [ ] Unique string ID required
- [ ] Name is required
- [ ] startDate and endDate optional (nullable)
- [ ] Defaults: progress=0, status="pending", priority="medium"
- [ ] Empty dependencies and parent on creation

**Test Data**:
| Input | Expected |
|-------|----------|
| id="task1", name="Design" | Task created |
| id="task1" (duplicate) | Error or replace |

---

### FR-TASK-002: Task Properties
**Priority**: High
**Description**: Tasks shall have configurable properties.

| Property | Type | Default | Validation |
|----------|------|---------|------------|
| id | string | required | Unique |
| name | string | required | Non-empty |
| startDate | ?DateTime | null | - |
| endDate | ?DateTime | null | - |
| progress | float | 0.0 | 0-100 |
| status | string | "pending" | enum |
| priority | string | "medium" | enum |
| assignee | string | "" | - |
| color | string | #3b82f6 | hex |
| estimatedHours | float | 0.0 | >= 0 |
| actualHours | float | 0.0 | >= 0 |

---

### FR-TASK-003: Task Progress
**Priority**: High
**Description**: System shall track task completion percentage.

**Acceptance Criteria**:
- [ ] setProgress(0-100) clamps value
- [ ] isCompleted() returns true if progress >= 100 or status="completed"
- [ ] isOverdue() returns true if endDate < now and not completed

---

### FR-TASK-004: Task Duration
**Priority**: Medium
**Description**: System shall calculate task duration in days.

**Acceptance Criteria**:
- [ ] getDuration() returns difference in days
- [ ] Returns null if startDate or endDate missing
- [ ] Handles single-day tasks (duration = 0)

---

### FR-TASK-005: Task Hierarchy
**Priority**: Medium
**Description**: Tasks shall support parent-child relationships.

**Acceptance Criteria**:
- [ ] setParentId() links task to parent
- [ ] getParentId() returns parent ID or null
- [ ] getSubtasks(parentId) returns child tasks
- [ ] getRootTasks() returns tasks without parent

---

### FR-TASK-006: Milestone Tasks
**Priority**: Medium
**Description**: Tasks shall support milestone markers.

**Acceptance Criteria**:
- [ ] isMileStone() returns boolean
- [ ] setMileStone(true) marks as milestone
- [ ] Milestones render as diamond markers
- [ ] Duration irrelevant for milestones

---

## 4. Dependency Management

### FR-DEP-001: Add Dependencies
**Priority**: High
**Description**: Tasks shall have finish-to-start dependencies.

**Acceptance Criteria**:
- [ ] addDependency(taskId) adds dependency
- [ ] Dependencies stored as array of task IDs
- [ ] Duplicate dependencies ignored
- [ ] Self-dependency should be prevented

**Example**:
```php
$taskB->addDependency("taskA"); // B depends on A
```

---

### FR-DEP-002: Dependency Validation
**Priority**: High
**Description**: System shall detect circular dependencies.

**Acceptance Criteria**:
- [ ] hasDependencyCycle() uses DFS algorithm
- [ ] Returns true if cycle detected
- [ ] Returns false if no cycle
- [ ] Prevents invalid dependency chains

**Test Scenario**:
```
TaskA → TaskB → TaskC
  ↑                  ↓
  └──────────────────┘  (cycle!)
```
**Expected**: hasDependencyCycle() = true

---

### FR-DEP-003: Topological Sort
**Priority**: Medium
**Description**: System shall sort tasks by dependencies.

**Acceptance Criteria**:
- [ ] topologicalSort() returns array of task IDs
- [ ] Tasks appear after their dependencies
- [ ] Throws RuntimeException on cycle
- [ ] Handles parallel tasks

---

### FR-DEP-004: Dependency Removal
**Priority**: Medium
**Description**: System shall remove task dependencies.

**Acceptance Criteria**:
- [ ] removeDependency(taskId) removes specific dependency
- [ ] Cascade: removing task removes it from dependents' dependencies
- [ ] Removing non-existent dependency has no effect

---

## 5. Rendering

### FR-RENDER-001: HTML Rendering
**Priority**: High
**Description**: System shall render Gantt chart as HTML.

**Acceptance Criteria**:
- [ ] renderHtml() returns complete HTML string
- [ ] Includes CSS styling
- [ ] Shows task bars with correct positions
- [ ] Shows timeline header with dates
- [ ] Weekend highlighting
- [ ] Responsive container

---

### FR-RENDER-002: SVG Rendering
**Priority**: Medium
**Description**: System shall render Gantt chart as SVG.

**Acceptance Criteria**:
- [ ] renderSvg() returns SVG markup
- [ ] Scalable vector graphics
- [ ] Correct bar positioning
- [ ] Color coding by status

---

### FR-RENDER-003: JSON Export
**Priority**: High
**Description**: System shall export chart as JSON.

**Acceptance Criteria**:
- [ ] toJson() returns valid JSON string
- [ ] Includes all chart and task data
- [ ] Uses ATOM date format
- [ ] Array of tasks with all properties

---

### FR-RENDER-004: FullCalendar Export
**Priority**: Medium
**Description**: System shall export to FullCalendar format.

**Acceptance Criteria**:
- [ ] toFullCalendar() returns array of events
- [ ] Each event has id, title, start, end, color
- [ ] Milestones included with allDay flag
- [ ] Progress mapped to event

---

### FR-RENDER-005: Color Coding
**Priority**: Medium
**Description**: Task bars shall be color-coded by status.

| Status | Color | Hex |
|--------|-------|-----|
| Pending | Blue | #3b82f6 |
| In Progress | Amber | #f59e0b |
| Completed | Green | #22c55e |
| Overdue | Red | #ef4444 |

**Acceptance Criteria**:
- [ ] Overdue tasks always red (regardless of progress)
- [ ] Completed tasks green
- [ ] In-progress tasks amber
- [ ] Custom color respected if set

---

## 6. Resource Utilization

### FR-RES-001: Utilization Calculation
**Priority**: High
**Description**: System shall calculate resource utilization.

**Acceptance Criteria**:
- [ ] calculateUtilization() returns per-assignee data
- [ ] Includes total hours, available hours, utilization %
- [ ] Excludes weekends from working days
- [ ] Status: overloaded, optimal, underutilized, free

---

### FR-RES-002: Daily Utilization
**Priority**: Medium
**Description**: System shall calculate daily resource usage.

**Acceptance Criteria**:
- [ ] getDailyUtilization(assignee, date) returns float
- [ ] Returns hours / maxDailyHours
- [ ] Returns 0 if no tasks on that day
- [ ] Default maxDailyHours = 8.0

---

### FR-RES-003: Overload Detection
**Priority**: High
**Description**: System shall identify overloaded resources.

**Acceptance Criteria**:
- [ ] getOverloadedResources() returns resources > threshold
- [ ] Default overloadThreshold = 1.0 (100%)
- [ ] Configurable via constructor
- [ ] Returns array with utilization data

---

### FR-RES-004: Underutilization Detection
**Priority**: Medium
**Description**: System shall identify underutilized resources.

**Acceptance Criteria**:
- [ ] getUnderutilizedResources() returns resources < threshold
- [ ] Default warningThreshold = 0.8 (80%)
- [ ] Excludes resources with no tasks
- [ ] Returns empty array if all optimal

---

### FR-RES-005: Capacity Planning
**Priority**: Medium
**Description**: System shall provide capacity planning data.

**Acceptance Criteria**:
- [ ] getCapacityPlanning() returns daily breakdown
- [ ] Each day shows total hours, per-resource hours
- [ ] Includes utilization status
- [ ] Configurable date range

---

### FR-RES-006: Resource Workload
**Priority**: Medium
**Description**: System shall provide per-resource workload summary.

**Acceptance Criteria**:
- [ ] getResourceWorkload() returns array
- [ ] Task count: total, completed, in_progress, pending
- [ ] Hours: estimated vs actual
- [ ] Efficiency ratio: actual / estimated

---

### FR-RES-007: CSV Export
**Priority**: Low
**Description**: System shall export utilization data as CSV.

**Acceptance Criteria**:
- [ ] exportToCsv() returns CSV string
- [ ] Headers: Assignee, Total Hours, Available, Utilization, Status
- [ ] Comma-separated with quoted values
- [ ] Line breaks for each resource

---

## 7. Task Filtering

### FR-FILTER-001: Filter by Status
**Priority**: Medium
**Description**: System shall filter tasks by status.

**Acceptance Criteria**:
- [ ] getTasksByStatus(status) returns matching tasks
- [ ] Status: pending, in_progress, completed

---

### FR-FILTER-002: Filter by Assignee
**Priority**: Medium
**Description**: System shall filter tasks by assignee.

**Acceptance Criteria**:
- [ ] getTasksByAssignee(assignee) returns matching tasks
- [ ] Case-sensitive matching
- [ ] Returns empty array if no matches

---

## 8. Serialization

### FR-SERIAL-001: Task to Array
**Priority**: High
**Description**: Tasks shall serialize to array.

**Acceptance Criteria**:
- [ ] toArray() returns associative array
- [ ] Includes all properties
- [ ] Dates formatted as ISO 8601

---

### FR-SERIAL-002: Task from Array
**Priority**: High
**Description**: Tasks shall deserialize from array.

**Acceptance Criteria**:
- [ ] fromArray(array) static method
- [ ] Parses dates if provided
- [ ] Sets all properties
- [ ] Returns GanttTask instance

---

### FR-SERIAL-003: Chart to Array
**Priority**: High
**Description**: Charts shall serialize to array.

**Acceptance Criteria**:
- [ ] toArray() returns associative array
- [ ] Includes all chart properties
- [ ] Includes serialized tasks array
- [ ] Includes calculated metrics

---

## 9. Acceptance Test Matrix

| FR ID | Requirement | Test Cases | Status |
|-------|-------------|------------|--------|
| FR-CHART-001 | Create Chart | CHART-001 | ✓ |
| FR-CHART-002 | Date Range | CHART-002 | ✓ |
| FR-TASK-001 | Create Task | TASK-001 | ✓ |
| FR-TASK-003 | Task Progress | TASK-003 | ✓ |
| FR-DEP-001 | Add Dependencies | DEP-001 | ✓ |
| FR-DEP-002 | Cycle Detection | DEP-002 | ✓ |
| FR-RENDER-001 | HTML Render | RENDER-001 | ✓ |
| FR-RES-001 | Utilization Calc | RES-001 | ✓ |

---

*Document Version: 1.0.0*
*Last Updated: 2026-05-13*