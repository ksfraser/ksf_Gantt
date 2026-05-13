# Use Case - ksf_Gantt

## Document Information
- **Module**: ksf_Gantt
- **Version**: 1.0.0
- **Date**: 2026-05-13
- **Status**: Implemented
- **Author**: KSFII Development Team

---

## 1. Use Case Overview

| Use Case ID | Use Case Name | Actor | Priority |
|-------------|---------------|-------|----------|
| UC-GANTT-001 | Create Gantt Chart | Project Manager | High |
| UC-GANTT-002 | Add Tasks | Project Manager | High |
| UC-GANTT-003 | Define Dependencies | Project Manager | High |
| UC-GANTT-004 | Update Task Progress | Team Member | High |
| UC-GANTT-005 | View Gantt Chart | Stakeholder | High |
| UC-GANTT-006 | Check Resource Utilization | Resource Manager | Medium |
| UC-GANTT-007 | Detect Dependency Cycles | System | High |
| UC-GANTT-008 | Export Chart Data | Developer | Medium |

---

## 2. Use Case Details

### UC-GANTT-001: Create Gantt Chart

**Actor**: Project Manager
**Priority**: High
**Preconditions**: None

**Basic Flow**:
1. Project Manager selects "Create New Project"
2. System prompts for project name
3. Project Manager enters name "Website Redesign"
4. System creates GanttChart with unique ID
5. System initializes empty task collection
6. System returns created chart

**Postconditions**: Chart exists, ready for tasks

---

### UC-GANTT-002: Add Tasks

**Actor**: Project Manager
**Priority**: High
**Preconditions**: Gantt chart exists

**Basic Flow**:
1. Project Manager opens chart
2. Clicks "Add Task"
3. Enters task details:
   - Name: "Design Homepage"
   - Start: 2026-05-15
   - End: 2026-05-22
   - Assignee: "Alice"
   - Priority: "High"
4. System creates GanttTask
5. System adds task to chart
6. System recalculates chart date range
7. System updates UI

**Alternative Flows**:
- **Missing dates**: Task created with null dates
- **Duplicate ID**: Return error

**Postconditions**: Task added, chart updated

---

### UC-GANTT-003: Define Dependencies

**Actor**: Project Manager
**Priority**: High
**Preconditions**: At least 2 tasks exist

**Basic Flow**:
1. Project Manager selects "Development" task
2. Opens dependency settings
3. Adds dependency on "Design" task
4. System validates no cycle created
5. System adds dependency
6. System updates UI

**Alternative Flows**:
- **Cycle detected**: System rejects dependency, shows warning

**Postconditions**: Dependency established

---

### UC-GANTT-004: Update Task Progress

**Actor**: Team Member
**Priority**: High
**Preconditions**: Task exists in chart

**Basic Flow**:
1. Team Member views Gantt chart
2. Clicks on "Development" task bar
3. Opens task details
4. Changes progress to 75%
5. System updates task progress
6. System recalculates overall progress
7. System updates color (amber if in_progress)

**Postconditions**: Progress updated, metrics recalculated

---

### UC-GANTT-005: View Gantt Chart

**Actor**: Stakeholder
**Priority**: High
**Preconditions**: Chart has tasks

**Basic Flow**:
1. Stakeholder opens project page
2. System loads GanttChart data
3. System calls GanttRenderer
4. System renders HTML
5. Chart displays with:
   - Timeline header with dates
   - Task bars with correct positioning
   - Color coding by status
   - Progress indicators

**Postconditions**: Chart visible

---

### UC-GANTT-006: Check Resource Utilization

**Actor**: Resource Manager
**Priority**: Medium
**Preconditions**: Tasks have assignees

**Basic Flow**:
1. Resource Manager opens "Resource View"
2. System calls ResourceUtilization service
3. System calculates per-resource metrics
4. System identifies overloaded resources (>100%)
5. System identifies underutilized resources (<80%)
6. System displays utilization report

**Postconditions**: Utilization data available

---

### UC-GANTT-007: Detect Dependency Cycles

**Actor**: System (automatic)
**Priority**: High
**Preconditions**: Dependency being added

**Basic Flow**:
1. User adds dependency A→B
2. System calls hasDependencyCycle()
3. System performs DFS traversal
4. No cycle found: dependency added
5. **OR** Cycle found: System throws RuntimeException
6. UI shows error message

**Example Cycle**:
```
A → B → C → A
```

**Postconditions**: Invalid dependencies prevented

---

### UC-GANTT-008: Export Chart Data

**Actor**: Developer
**Priority**: Medium
**Preconditions**: Chart exists

**Basic Flow**:
1. Developer calls chart.toJson()
2. System serializes chart
3. JSON returned
4. Developer uses in frontend

**Alternative Formats**:
- toFullCalendar() → FullCalendar.js events
- exportToCsv() → CSV file for reporting

**Postconditions**: Data exported

---

## 3. Sequence Diagrams

### UC-GANTT-005: View Gantt Chart

```
┌─────────────┐    ┌──────────────┐    ┌────────────┐    ┌─────────────┐
│  Browser    │    │  Controller  │    │ GanttChart │    │ Renderer   │
└─────────────┘    └──────────────┘    └────────────┘    └─────────────┘
       │                 │                 │                 │
       │ GET /gantt/1    │                 │                 │
       │────────────────>│                 │                 │
       │                 │                 │                 │
       │                 │ load(1)         │                 │
       │                 │────────────────>│                 │
       │                 │                 │                 │
       │                 │<────────────────│                 │
       │                 │ Chart data       │                 │
       │                 │                 │                 │
       │                 │ renderHtml()     │                 │
       │                 │───────────────────────────────>│   │
       │                 │                 │                 │
       │                 │                 │                 │
       │                 │                 │<─────────────────│
       │                 │                 │  HTML string    │
       │                 │                 │                 │
       │ HTML            │                 │                 │
       │<────────────────│                 │                 │
```

### UC-GANTT-006: Check Resource Utilization

```
┌─────────────┐    ┌──────────────┐    ┌────────────┐    ┌─────────────┐
│  Resource   │    │  Resource    │    │  Resource  │    │  GanttChart │
│  Manager    │    │  Controller  │    │ Utilization│    │             │
└─────────────┘    └──────────────┘    └────────────┘    └─────────────┘
       │                 │                 │                 │
       │ View Resources │                 │                 │
       │────────────────>│                 │                 │
       │                 │                 │                 │
       │                 │ calculateUtil() │                 │
       │                 │────────────────>│                 │
       │                 │                 │                 │
       │                 │                 │ getTasks()      │
       │                 │                 │────────────────>│
       │                 │                 │                 │
       │                 │                 │<────────────────│
       │                 │                 │                 │
       │                 │<────────────────│                 │
       │                 │ Utilization      │                 │
       │                 │ report           │                 │
       │ Display         │                 │                 │
       │<────────────────│                 │                 │
```

---

## 4. Activity Diagram

### Task Addition Process

```
[Start] ──> [Click Add Task]
                    │
                    ▼
           ┌──────────────────┐
           │ Enter Task Name  │
           │ Enter Dates      │
           │ Assign Resource  │
           └──────────────────┘
                    │
                    ▼
            [Create GanttTask]
                    │
                    ▼
            ┌──────────────────┐
            │ Chart.addTask()  │
            └──────────────────┘
                    │
                    ▼
            ┌──────────────────┐
            │ Recalculate      │
            │ Date Range       │
            └──────────────────┘
                    │
                    ▼
            ┌──────────────────┐
            │ Dependency Valid?│
            └──────────────────┘
                    │
                 Yes│
                    ▼
            ┌──────────────────┐
            │ Update UI        │
            │ Show new task    │
            └──────────────────┘
                    │
                    ▼
                  [End]
```

### Dependency Cycle Detection

```
[Start] ──> [Add Dependency]
                    │
                    ▼
          ┌───────────────────┐
          │ Build dependency  │
          │ graph              │
          └───────────────────┘
                    │
                    ▼
          ┌───────────────────┐
          │ DFS Traversal      │
          │ Track visited      │
          │ Track recursion     │
          └───────────────────┘
                    │
                    ▼
          ┌───────────────────┐
          │ Cycle detected?    │
          └───────────────────┘
               │        │
             Yes       No
               │        │
               ▼        ▼
      ┌──────────┐  ┌────────────┐
      │ Reject   │  │ Accept     │
      │ Show err │  │ Add dep    │
      └──────────┘  └────────────┘
               │        │
               └────┬───┘
                    ▼
                 [End]
```

---

## 5. Data Requirements

### 5.1 Input Data

| Data | Source | Required | Format |
|------|--------|----------|--------|
| Chart ID | User/System | Yes | String |
| Chart Name | User | Yes | String (255) |
| Task ID | User/System | Yes | String |
| Task Name | User | Yes | String |
| Start Date | User | No | DateTime |
| End Date | User | No | DateTime |
| Assignee | User | No | String |
| Dependencies | User | No | Array |

### 5.2 Output Data

| Data | Format | Usage |
|------|--------|-------|
| HTML Chart | String | Web display |
| SVG Chart | String | Vector graphics |
| JSON | String | API integration |
| CSV | String | Reports |
| FullCalendar Events | Array | Calendar integration |

---

## 6. Non-Functional Requirements

### 6.1 Performance
- Chart render (100 tasks): < 200ms
- Cycle detection: < 50ms
- Resource calculation: < 100ms

### 6.2 Scalability
- Max recommended tasks: 500
- Memory: O(n) where n = tasks

### 6.3 Usability
- Color-coded status
- Progress indicators
- Tooltips on hover
- Responsive design

---

## 7. Use Case Traceability

| UC ID | Related FR | Related Test |
|-------|------------|-------------|
| UC-GANTT-001 | FR-CHART-001 | CHART-001 |
| UC-GANTT-002 | FR-TASK-001, FR-TASK-002 | TASK-001 |
| UC-GANTT-003 | FR-DEP-001, FR-DEP-002 | DEP-001, DEP-002 |
| UC-GANTT-004 | FR-TASK-003 | TASK-003 |
| UC-GANTT-005 | FR-RENDER-001 | RENDER-001 |
| UC-GANTT-006 | FR-RES-001, FR-RES-003 | RES-001 |
| UC-GANTT-007 | FR-DEP-002 | DEP-002 |
| UC-GANTT-008 | FR-RENDER-003 | RENDER-003 |

---

*Document Version: 1.0.0*
*Last Updated: 2026-05-13*