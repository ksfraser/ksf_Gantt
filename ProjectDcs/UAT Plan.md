# UAT Plan - ksf_Gantt

## Document Information
- **Module**: ksf_Gantt
- **Version**: 1.0.0
- **Date**: 2026-05-13
- **Status**: Ready for UAT
- **Author**: KSFII Development Team

---

## 1. UAT Objectives

### 1.1 Purpose
Validate that ksf_Gantt correctly creates, manages, and visualizes Gantt charts with proper task handling, dependencies, and resource tracking.

### 1.2 Objectives
1. Verify Gantt chart creation and management
2. Confirm task operations work correctly
3. Validate dependency management
4. Ensure rendering displays charts correctly
5. Verify resource utilization calculations

---

## 2. Test Scenarios

### 2.1 Chart Management

#### UAT-GANTT-001: Create New Gantt Chart
**Scenario**: Project Manager creates new project timeline

**Preconditions**: User has access to Gantt module

**Test Steps**:
1. Click "Create New Project"
2. Enter project name: "Website Redesign 2026"
3. Click "Create"
4. Verify chart created with correct name
5. Verify empty task list shown

**Expected Result**: Empty chart created successfully

**Pass Criteria**: [ ] Chart created [ ] Name correct [ ] Empty state shown

---

#### UAT-GANTT-002: View Chart Date Range
**Scenario**: System calculates correct date range

**Preconditions**: Chart with tasks exists

**Test Steps**:
1. Open existing project
2. Verify start date = earliest task start
3. Verify end date = latest task end
4. Add task with earlier start date
5. Verify start date updated

**Expected Result**: Date range auto-calculated correctly

**Pass Criteria**: [ ] Start correct [ ] End correct [ ] Updates on add

---

### 2.2 Task Management

#### UAT-GANTT-003: Add Task to Chart
**Scenario**: Project Manager adds a new task

**Preconditions**: Chart exists

**Test Steps**:
1. Click "Add Task"
2. Enter name: "Design Homepage"
3. Set start date: 2026-05-15
4. Set end date: 2026-05-22
5. Assign to: Alice
6. Set priority: High
7. Click "Save"
8. Verify task appears in chart

**Expected Result**: Task added with all properties

**Pass Criteria**: [ ] Task created [ ] Dates set [ ] Assignee shown [ ] Color coded

---

#### UAT-GANTT-004: Update Task Progress
**Scenario**: Team member updates work completion

**Preconditions**: Task exists in chart

**Test Steps**:
1. Locate task "Development" in chart
2. Click task bar
3. Update progress to 75%
4. Save changes
5. Verify progress bar updates
6. Verify overall chart progress updates

**Expected Result**: Progress tracked and displayed

**Pass Criteria**: [ ] Progress saved [ ] Bar updated [ ] Overall updated

---

#### UAT-GANTT-005: Mark Task Complete
**Scenario**: Task finishes, marked complete

**Preconditions**: Task with progress < 100%

**Test Steps**:
1. Open task details
2. Set progress to 100%
3. Verify status changes to "Completed"
4. Verify color changes to green
5. Verify completed count increments

**Expected Result**: Task marked complete with green indicator

**Pass Criteria**: [ ] Status updated [ ] Green color [ ] Count updated

---

#### UAT-GANTT-006: Create Subtask
**Scenario**: Breaking down large task into subtasks

**Preconditions**: Parent task exists

**Test Steps**:
1. Select parent task
2. Click "Add Subtask"
3. Enter subtask details
4. Verify subtask linked to parent
5. Verify subtask appears indented in list
6. Verify subtask shows in chart

**Expected Result**: Hierarchical task structure

**Pass Criteria**: [ ] Subtask created [ ] Parent linked [ ] Indented view

---

#### UAT-GANTT-007: Create Milestone
**Scenario**: Mark important project milestone

**Preconditions**: Chart exists

**Test Steps**:
1. Click "Add Milestone"
2. Enter name: "Project Launch"
3. Set date: 2026-06-01
4. Save
5. Verify milestone appears as diamond marker
6. Verify milestone in timeline

**Expected Result**: Milestone displayed as diamond

**Pass Criteria**: [ ] Milestone created [ ] Diamond marker [ ] Position correct

---

### 2.3 Dependency Management

#### UAT-GANTT-008: Add Task Dependency
**Scenario**: Tasks with sequential relationship

**Preconditions**: Two tasks exist

**Test Steps**:
1. Select "Development" task
2. Open dependencies
3. Add dependency on "Design" task
4. Verify dependency arrow appears
5. Verify "Development" starts when "Design" ends

**Expected Result**: Dependency established, arrow shown

**Pass Criteria**: [ ] Dependency saved [ ] Arrow rendered [ ] Visual linked

---

#### UAT-GANTT-009: Prevent Circular Dependency
**Scenario**: System prevents impossible dependencies

**Preconditions**: Tasks A → B → C

**Test Steps**:
1. Select task A
2. Try to add dependency on task C
3. This would create cycle: A → B → C → A
4. System should reject

**Expected Result**: Error message, dependency not added

**Pass Criteria**: [ ] Cycle detected [ ] Error shown [ ] Dependency rejected

---

#### UAT-GANTT-010: Remove Dependency
**Scenario**: Change task relationships

**Preconditions**: Task has dependency

**Test Steps**:
1. Open task with dependency
2. Remove dependency
3. Save
4. Verify dependency arrow removed
5. Verify tasks no longer linked

**Expected Result**: Dependency removed

**Pass Criteria**: [ ] Dependency removed [ ] Arrow gone [ ] Tasks independent

---

### 2.4 Rendering

#### UAT-GANTT-011: View Gantt HTML
**Scenario**: Display chart as HTML

**Preconditions**: Chart with tasks exists

**Test Steps**:
1. Open chart view
2. Verify timeline header with dates
3. Verify task bars positioned correctly
4. Verify color coding (blue pending, amber in progress, green complete)
5. Verify progress bars in task bars

**Expected Result**: Professional HTML Gantt display

**Pass Criteria**: [ ] Timeline correct [ ] Positions correct [ ] Colors correct [ ] Progress shown

---

#### UAT-GANTT-012: Weekend Highlighting
**Scenario**: Visual distinction for weekends

**Preconditions**: Chart spanning weekdays and weekends

**Test Steps**:
1. View chart
2. Locate Saturday and Sunday columns
3. Verify weekend columns have different background
4. Verify tasks don't extend over weekend incorrectly

**Expected Result**: Weekends visually distinguished

**Pass Criteria**: [ ] Different background [ ] Consistent styling

---

#### UAT-GANTT-013: Export to JSON
**Scenario**: Export chart data for external use

**Preconditions**: Chart with tasks

**Test Steps**:
1. Click "Export" menu
2. Select "JSON"
3. Copy or download JSON
4. Validate JSON structure
5. Verify includes chart and tasks

**Expected Result**: Valid JSON with complete data

**Pass Criteria**: [ ] Valid JSON [ ] All data included [ ] Proper dates

---

#### UAT-GANTT-014: FullCalendar Integration
**Scenario**: Export to FullCalendar format

**Preconditions**: Chart exists

**Test Steps**:
1. Export as FullCalendar
2. Verify events array format
3. Verify each event has id, title, start, end
4. Verify colors match status

**Expected Result**: FullCalendar-ready format

**Pass Criteria**: [ ] Valid array [ ] Correct fields [ ] Colors mapped

---

### 2.5 Resource Utilization

#### UAT-GANTT-015: View Resource Load
**Scenario**: Check team member workload

**Preconditions**: Tasks have assignees

**Test Steps**:
1. Open Resource View
2. Select "Alice" from resource list
3. Verify tasks assigned to Alice listed
4. Verify total hours calculated
5. Verify utilization percentage shown

**Expected Result**: Resource workload summary

**Pass Criteria**: [ ] Tasks listed [ ] Hours correct [ ] Utilization shown

---

#### UAT-GANTT-016: Identify Overloaded Resource
**Scenario**: Find resources with too much work

**Preconditions**: Resource assigned > 100% capacity

**Test Steps**:
1. Open Resource Utilization report
2. Look for resources marked "Overloaded"
3. Verify overloaded resources highlighted
4. Verify shows > 100% utilization

**Expected Result**: Overloaded resources identified

**Pass Criteria**: [ ] Overloaded marked [ ] Percentage > 100% [ ] Tasks listed

---

#### UAT-GANTT-017: Capacity Planning View
**Scenario**: View daily capacity forecast

**Preconditions**: Multiple resources with tasks

**Test Steps**:
1. Open Capacity Planning
2. Set date range: next 2 weeks
3. View daily breakdown
4. Verify each day shows total hours
5. Verify per-resource hours shown
6. Verify status (green/amber/red) per day

**Expected Result**: Capacity forecast with daily breakdown

**Pass Criteria**: [ ] Date range shown [ ] Daily totals [ ] Per-resource [ ] Status indicators

---

#### UAT-GANTT-018: CSV Export
**Scenario**: Export utilization data for reports

**Preconditions**: Resource data exists

**Test Steps**:
1. Open Resource Utilization
2. Click "Export CSV"
3. Open file
4. Verify columns: Assignee, Total Hours, Available, Utilization, Status
5. Verify data rows correct

**Expected Result**: Valid CSV with utilization data

**Pass Criteria**: [ ] Valid CSV [ ] Headers correct [ ] Data accurate

---

## 3. Test Execution Schedule

### 3.1 Phase 1: Core Functionality (Day 1)
| Test | Focus |
|------|-------|
| UAT-GANTT-001 | Chart creation |
| UAT-GANTT-003 | Task addition |
| UAT-GANTT-004 | Progress tracking |
| UAT-GANTT-005 | Task completion |

### 3.2 Phase 2: Dependencies (Day 1)
| Test | Focus |
|------|-------|
| UAT-GANTT-008 | Dependency creation |
| UAT-GANTT-009 | Cycle prevention |
| UAT-GANTT-010 | Dependency removal |

### 3.3 Phase 3: Rendering (Day 2)
| Test | Focus |
|------|-------|
| UAT-GANTT-011 | HTML view |
| UAT-GANTT-012 | Weekend styling |
| UAT-GANTT-013 | JSON export |
| UAT-GANTT-014 | FullCalendar |

### 3.4 Phase 4: Resources (Day 2)
| Test | Focus |
|------|-------|
| UAT-GANTT-015 | Load view |
| UAT-GANTT-016 | Overload detection |
| UAT-GANTT-017 | Capacity planning |
| UAT-GANTT-018 | CSV export |

---

## 4. Success Criteria

### 4.1 Functional Criteria

| Criteria | Target | Actual |
|----------|--------|--------|
| Chart creation | 100% | - |
| Task operations | 100% | - |
| Dependency handling | 100% | - |
| Rendering accuracy | 100% | - |
| Resource calculations | 100% | - |

### 4.2 Test Summary

| Category | Total | Passed | Failed |
|----------|-------|--------|--------|
| Chart Management | 2 | - | - |
| Task Management | 5 | - | - |
| Dependencies | 3 | - | - |
| Rendering | 4 | - | - |
| Resources | 4 | - | - |
| **Total** | **18** | **-** | **-** |

---

## 5. Sign-off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Business Owner | | | |
| Project Manager | | | |
| QA Lead | | | |
| Technical Lead | | | |

---

## 6. Appendix

### 6.1 Test Data

| Element | Test Value |
|---------|------------|
| Chart Name | "Website Redesign 2026" |
| Task Name | "Design Phase" |
| Assignee | Alice |
| Start Date | 2026-05-15 |
| End Date | 2026-05-22 |

### 6.2 Browser Compatibility
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

---

*Document Version: 1.0.0*
*Last Updated: 2026-05-13*