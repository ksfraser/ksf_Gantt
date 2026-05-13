# Business Requirements - ksf_Gantt

## Document Information
- **Module**: ksf_Gantt
- **Version**: 1.0.0
- **Date**: 2026-05-13
- **Status**: Implemented
- **Author**: KSFII Development Team

---

## 1. Project Overview

### 1.1 Purpose
The ksf_Gantt module provides comprehensive Gantt chart visualization and project task management functionality for the KSF ecosystem. It enables creation, management, and rendering of project timelines with task dependencies, resource allocation, and progress tracking.

### 1.2 Business Problem Statement
Organizations need to visualize project schedules and track task progress. The ksf_Gantt module provides:
- Visual timeline representation of project tasks
- Task dependency management
- Resource utilization tracking
- Progress monitoring
- Multiple output formats (HTML, SVG, JSON, FullCalendar)

### 1.3 Scope

| Category | Included |
|----------|----------|
| Gantt Chart Creation | Yes |
| Task Management | Yes |
| Dependencies | Yes |
| Progress Tracking | Yes |
| Resource Utilization | Yes |
| Multiple Render Formats | Yes |
| Milestones | Yes |
| Task Assignment | Yes |

---

## 2. Module Architecture

### 2.1 Namespace Structure
```
Ksfraser\Gantt\
├── Entity\
│   ├── GanttChart.php     # Chart container
│   └── GanttTask.php      # Individual task
└── Service\
    ├── GanttRenderer.php       # HTML/SVG/JSON rendering
    └── ResourceUtilization.php # Resource tracking
```

### 2.2 Core Entities

#### GanttChart Entity
The `GanttChart` class represents a project timeline with task collection:

| Property | Type | Description |
|----------|------|-------------|
| id | string | Unique chart identifier |
| name | string | Chart/project name |
| startDate | ?DateTime | Earliest task start date |
| endDate | ?DateTime | Latest task end date |
| tasks | array | Collection of GanttTask entities |
| timezone | string | Timezone for date calculations |

#### GanttTask Entity
Individual task within a Gantt chart:

| Property | Type | Description |
|----------|------|-------------|
| id | string | Task identifier |
| name | string | Task name |
| startDate | ?DateTime | Task start date |
| endDate | ?DateTime | Task end date |
| progress | float | Completion percentage (0-100) |
| status | string | pending, in_progress, completed |
| priority | string | low, medium, high, critical |
| assignee | string | Assigned resource |
| dependencies | array | Task IDs this depends on |
| parentId | ?string | Parent task for subtasks |
| color | string | Bar color (hex) |
| isMileStone | bool | Milestone marker |
| estimatedHours | float | Estimated hours |
| actualHours | float | Actual hours spent |

---

## 3. Functional Features

### 3.1 Task Management

| Feature | Description |
|---------|-------------|
| Create Task | Add task with dates, name, assignee |
| Update Task | Modify task properties |
| Delete Task | Remove task (cascades to dependents) |
| Task Hierarchy | Parent-child relationships |
| Milestones | Zero-duration markers |
| Dependencies | Finish-to-start relationships |

### 3.2 Dependency Management

| Type | Description |
|------|-------------|
| Finish-to-Start | Task B starts when Task A finishes |
| Start-to-Start | Task B starts when Task A starts |
| Dependency Chain | Multiple tasks linked in sequence |

**Cycle Detection**: System detects circular dependencies and prevents them.

### 3.3 Progress Tracking

| Metric | Description |
|--------|-------------|
| Progress % | Per-task completion (0-100) |
| Overall Progress | Chart-wide average |
| Completed Count | Number of 100% tasks |
| Overdue Status | Tasks past end date with <100% |

### 3.4 Resource Utilization

| Feature | Description |
|---------|-------------|
| Assignee Tracking | Tasks linked to resources |
| Utilization Calculation | Hours per resource per day |
| Overload Detection | Resources exceeding capacity |
| Underutilization | Resources below threshold |
| Capacity Planning | Daily capacity projections |

**Thresholds**:
- Warning: 80% utilization
- Overload: 100%+ utilization

### 3.5 Rendering Formats

| Format | Description |
|--------|-------------|
| HTML | Interactive web display |
| SVG | Scalable vector graphics |
| JSON | API/frontend integration |
| FullCalendar | Calendar library format |

---

## 4. Integration Dependencies

### 4.1 Provided To

| Module | Data/Events |
|--------|-------------|
| ksf_FA_ProjectManagement | Gantt data, task events |
| ksf_FA_Timesheets | Resource allocation data |

### 4.2 External Integrations

| System | Integration Type | Description |
|--------|------------------|-------------|
| FullCalendar | Export | Load into FullCalendar.js |
| Project Management Tools | JSON API | External integrations |

---

## 5. Data Flow

### 5.1 Task Creation Flow

```
User creates task
    ↓
GanttTask instantiated
    ↓
Dates and properties set
    ↓
Task added to GanttChart
    ↓
Date range recalculated
    ↓
Dependency validation (cycle check)
    ↓
Chart updated
```

### 5.2 Progress Update Flow

```
Progress updated on task
    ↓
Chart calculates overall progress
    ↓
Completed count updated
    ↓
Overdue status recalculated
    ↓
Resource utilization updated
    ↓
UI re-rendered
```

---

## 6. Configuration

### 6.1 Chart Configuration

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| dayWidth | int | 40 | Pixels per day |
| rowHeight | int | 40 | Task row height |
| headerHeight | int | 50 | Timeline header height |
| sidebarWidth | int | 250 | Task name column width |

### 6.2 Color Scheme

| Status | Color | Hex |
|--------|-------|-----|
| Pending | Blue | #3b82f6 |
| In Progress | Amber | #f59e0b |
| Completed | Green | #22c55e |
| Overdue | Red | #ef4444 |
| Custom | User-defined | Any hex |

---

## 7. Non-Functional Requirements

### 7.1 Performance
- Chart with 100 tasks: < 200ms render
- Dependency cycle check: < 50ms
- Resource calculation: < 100ms

### 7.2 Scalability
- Support 1000+ tasks per chart
- Efficient dependency resolution

### 7.3 Timezone Support
- Default: UTC
- Configurable per chart

---

## 8. Future Enhancements

| Feature | Priority | Description |
|---------|----------|-------------|
| Drag-and-drop | High | Reschedule tasks visually |
| Critical Path | Medium | Calculate critical path |
| Baseline | Medium | Compare to original plan |
| Export PDF | Low | Generate PDF reports |

---

## 9. Sign-off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Business Analyst | | | |
| Technical Lead | | | |
| QA Lead | | | |

---

*Document Version: 1.0.0*
*Last Updated: 2026-05-13*