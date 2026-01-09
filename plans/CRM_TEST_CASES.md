# CRM Module Test Cases

**Last Updated:** 2026-01-09
**Module:** `modules/crm/`
**Total Controllers:** 26
**Total Actions:** 208+
**Test Cases:** ~365

---

## Table of Contents

1. [Testing Guidelines](#testing-guidelines)
2. [Dashboard (DefaultController)](#1-dashboard-defaultcontroller)
3. [Pupils (PupilController)](#2-pupils-pupilcontroller)
4. [Groups (GroupController)](#3-groups-groupcontroller)
5. [Schedule (ScheduleController)](#4-schedule-schedulecontroller)
6. [Leads (LidsController)](#5-leads-lidscontroller)
7. [Leads Kanban (LidsFunnelController)](#6-leads-kanban-lidsfunnelcontroller)
8. [Leads Interaction (LidsInteractionController)](#7-leads-interaction-lidsinteractioncontroller)
9. [Lead Tags (LidTagController)](#8-lead-tags-lidtagcontroller)
10. [Sales Scripts (SalesScriptController)](#9-sales-scripts-salesscriptcontroller)
11. [Payments (PaymentController)](#10-payments-paymentcontroller)
12. [Tariffs (TariffController)](#11-tariffs-tariffcontroller)
13. [Subjects (SubjectController)](#12-subjects-subjectcontroller)
14. [Users (UserController)](#13-users-usercontroller)
15. [Salaries (SalaryController)](#14-salaries-salarycontroller)
16. [Reports (ReportsController)](#15-reports-reportscontroller)
17. [Attendance (AttendanceController)](#16-attendance-attendancecontroller)
18. [Rooms (RoomController)](#17-rooms-roomcontroller)
19. [Pay Methods (PayMethodController)](#18-pay-methods-paymethodcontroller)
20. [Schedule Templates (ScheduleTemplateController)](#19-schedule-templates-scheduletemplatecontroller)
21. [Settings (SettingsController)](#20-settings-settingscontroller)
22. [SMS (SmsController)](#21-sms-smscontroller)
23. [WhatsApp (WhatsappController)](#22-whatsapp-whatsappcontroller)
24. [Notifications (NotificationController)](#23-notifications-notificationcontroller)
25. [Subscription (SubscriptionController)](#24-subscription-subscriptioncontroller)
26. [Knowledge Base (KnowledgeController)](#25-knowledge-base-knowledgecontroller)
27. [Spelling Verification](#spelling-verification)
28. [UI/UX Checklist](#uiux-checklist)
29. [RBAC Tests](#rbac-tests)

---

## Testing Guidelines

### Priority Levels
- **P1 (Critical):** Core functionality, data integrity, security
- **P2 (High):** Main user flows, important features
- **P3 (Medium):** Secondary features, edge cases
- **P4 (Low):** Minor UI issues, cosmetic bugs

### Test Result Markers
- [ ] Not tested
- [x] Passed
- [!] Failed - needs fix
- [-] Skipped / Not applicable

### Test Categories
- **FUNC:** Functional test
- **UI:** User interface test
- **SPELL:** Spelling/grammar verification
- **RBAC:** Permission/access control test
- **HINT:** Tooltip/hint presence test

---

## 1. Dashboard (DefaultController)

**URL:** `/<oid>/`
**Actions:** 1

### Functional Tests

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| DASH-001 | Dashboard loads successfully | P1 | FUNC | [ ] |
| DASH-002 | Statistics widgets display correct data | P1 | FUNC | [ ] |
| DASH-003 | Quick action buttons are functional | P2 | FUNC | [ ] |
| DASH-004 | Recent activity widget shows latest entries | P2 | FUNC | [ ] |
| DASH-005 | Charts render correctly | P2 | FUNC | [ ] |

### UI Tests

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| DASH-UI-001 | Dashboard layout is responsive | P2 | UI | [ ] |
| DASH-UI-002 | Widgets have consistent styling | P3 | UI | [ ] |
| DASH-UI-003 | Loading states display while data fetches | P3 | UI | [ ] |

---

## 2. Pupils (PupilController)

**URL:** `/<oid>/pupil/`
**Actions:** 12 (index, view, create, update, delete, edu, create-edu, update-edu, copy-edu, delete-edu, payment, create-payment)

### CRUD Operations

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| PUP-001 | View list of pupils | P1 | FUNC | [ ] |
| PUP-002 | Search pupils by name | P1 | FUNC | [ ] |
| PUP-003 | Filter pupils by status | P2 | FUNC | [ ] |
| PUP-004 | Pagination works correctly | P2 | FUNC | [ ] |
| PUP-005 | View single pupil details | P1 | FUNC | [ ] |
| PUP-006 | Create new pupil with valid data | P1 | FUNC | [ ] |
| PUP-007 | Create pupil - required field validation | P1 | FUNC | [ ] |
| PUP-008 | Create pupil - phone format validation | P2 | FUNC | [ ] |
| PUP-009 | Update existing pupil | P1 | FUNC | [ ] |
| PUP-010 | Delete pupil (soft delete) | P1 | FUNC | [ ] |
| PUP-011 | Delete pupil confirmation dialog | P2 | FUNC | [ ] |

### Education Management

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| PUP-EDU-001 | View pupil education records | P1 | FUNC | [ ] |
| PUP-EDU-002 | Add new education record | P1 | FUNC | [ ] |
| PUP-EDU-003 | Select tariff for education | P1 | FUNC | [ ] |
| PUP-EDU-004 | Select group for education | P1 | FUNC | [ ] |
| PUP-EDU-005 | Update education record | P2 | FUNC | [ ] |
| PUP-EDU-006 | Copy education to new period | P2 | FUNC | [ ] |
| PUP-EDU-007 | Delete education record | P2 | FUNC | [ ] |
| PUP-EDU-008 | Education dates validation | P2 | FUNC | [ ] |

### Payment Management

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| PUP-PAY-001 | View pupil payment history | P1 | FUNC | [ ] |
| PUP-PAY-002 | Add payment (type=pay) | P1 | FUNC | [ ] |
| PUP-PAY-003 | Add refund (type=refund) | P1 | FUNC | [ ] |
| PUP-PAY-004 | Payment amount validation (min > 0) | P1 | FUNC | [ ] |
| PUP-PAY-005 | Payment date/time picker works | P2 | FUNC | [ ] |
| PUP-PAY-006 | Select payment method | P1 | FUNC | [ ] |
| PUP-PAY-007 | Select payment purpose | P1 | FUNC | [ ] |
| PUP-PAY-008 | Balance recalculates after payment | P1 | FUNC | [ ] |
| PUP-PAY-009 | Warning when no payment methods configured | P2 | FUNC | [ ] |

### UI Tests

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| PUP-UI-001 | Pupil list table is responsive | P2 | UI | [ ] |
| PUP-UI-002 | Form validation errors display correctly | P2 | UI | [ ] |
| PUP-UI-003 | Success messages appear after save | P2 | UI | [ ] |
| PUP-UI-004 | Loading spinner during form submit | P3 | UI | [ ] |
| PUP-UI-005 | Modal dialogs open/close properly | P2 | UI | [ ] |

### Hints/Tooltips

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| PUP-HINT-001 | Phone field has format hint | P3 | HINT | [ ] |
| PUP-HINT-002 | Required fields are clearly marked | P2 | HINT | [ ] |
| PUP-HINT-003 | Date fields have placeholder | P3 | HINT | [ ] |

---

## 3. Groups (GroupController)

**URL:** `/<oid>/group/`
**Actions:** 11 (index, my-groups, view, teachers, pupils, create, update, delete, delete-teacher, create-teacher)

### CRUD Operations

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| GRP-001 | View list of groups | P1 | FUNC | [ ] |
| GRP-002 | Filter groups by subject | P2 | FUNC | [ ] |
| GRP-003 | Filter groups by status (active/inactive) | P2 | FUNC | [ ] |
| GRP-004 | View single group details | P1 | FUNC | [ ] |
| GRP-005 | Create new group | P1 | FUNC | [ ] |
| GRP-006 | Update group | P1 | FUNC | [ ] |
| GRP-007 | Delete group | P2 | FUNC | [ ] |
| GRP-008 | View my groups (teacher view) | P2 | FUNC | [ ] |

### Teachers Management

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| GRP-TCH-001 | View teachers assigned to group | P1 | FUNC | [ ] |
| GRP-TCH-002 | Add teacher to group | P1 | FUNC | [ ] |
| GRP-TCH-003 | Remove teacher from group | P2 | FUNC | [ ] |

### Pupils Management

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| GRP-PUP-001 | View pupils in group | P1 | FUNC | [ ] |
| GRP-PUP-002 | Pupils list shows attendance status | P2 | FUNC | [ ] |

### UI Tests

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| GRP-UI-001 | Group cards display correctly | P2 | UI | [ ] |
| GRP-UI-002 | Empty state message when no groups | P3 | UI | [ ] |

---

## 4. Schedule (ScheduleController)

**URL:** `/<oid>/schedule/`
**Actions:** 17 (index, events, filters, details, ajax-create, ajax-update, ajax-delete, move, teachers, rooms, check-conflicts, view, create, update, delete, settings, save-settings)

### Calendar Display

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| SCH-001 | Calendar view loads | P1 | FUNC | [ ] |
| SCH-002 | Switch between week/day/month views | P2 | FUNC | [ ] |
| SCH-003 | Navigate to previous/next periods | P1 | FUNC | [ ] |
| SCH-004 | Today button works | P2 | FUNC | [ ] |
| SCH-005 | Events display on correct dates/times | P1 | FUNC | [ ] |
| SCH-006 | Events show group/teacher info | P1 | FUNC | [ ] |

### Lesson Management

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| SCH-007 | Create lesson via calendar click | P1 | FUNC | [ ] |
| SCH-008 | Create lesson form validation | P1 | FUNC | [ ] |
| SCH-009 | Update lesson details | P1 | FUNC | [ ] |
| SCH-010 | Delete lesson | P1 | FUNC | [ ] |
| SCH-011 | Drag and drop lesson to new time | P2 | FUNC | [ ] |
| SCH-012 | Conflict detection (room/teacher overlap) | P1 | FUNC | [ ] |

### Filters

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| SCH-013 | Filter by teacher | P2 | FUNC | [ ] |
| SCH-014 | Filter by room | P2 | FUNC | [ ] |
| SCH-015 | Filter by group | P2 | FUNC | [ ] |

### UI Tests

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| SCH-UI-001 | Calendar is responsive | P2 | UI | [ ] |
| SCH-UI-002 | Event colors by group/subject | P3 | UI | [ ] |
| SCH-UI-003 | Loading indicator during fetch | P3 | UI | [ ] |

---

## 5. Leads (LidsController)

**URL:** `/<oid>/lids/`
**Actions:** 8 (index, view, create, update, delete, create-ajax, update-ajax, get-lid)

### CRUD Operations

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| LID-001 | View leads list | P1 | FUNC | [ ] |
| LID-002 | Search leads by name/phone | P1 | FUNC | [ ] |
| LID-003 | Filter by status | P1 | FUNC | [ ] |
| LID-004 | Filter by source | P2 | FUNC | [ ] |
| LID-005 | Create new lead | P1 | FUNC | [ ] |
| LID-006 | Lead form validation | P1 | FUNC | [ ] |
| LID-007 | Update lead | P1 | FUNC | [ ] |
| LID-008 | Delete lead | P2 | FUNC | [ ] |
| LID-009 | View lead details modal | P1 | FUNC | [ ] |
| LID-010 | AJAX create from kanban | P2 | FUNC | [ ] |
| LID-011 | AJAX update from kanban | P2 | FUNC | [ ] |

### UI Tests

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| LID-UI-001 | Lead cards display correctly | P2 | UI | [ ] |
| LID-UI-002 | Status badges have correct colors | P3 | UI | [ ] |
| LID-UI-003 | Quick actions visible on hover | P3 | UI | [ ] |

---

## 6. Leads Kanban (LidsFunnelController)

**URL:** `/<oid>/lids-funnel/`
**Actions:** 11 (kanban, analytics, change-status, convert-ajax, get-conversion-data, get-groups-by-tariff, get-sales-script, get-all-sales-scripts)

### Kanban Board

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| KAN-001 | Kanban board loads | P1 | FUNC | [ ] |
| KAN-002 | Leads display in correct status columns | P1 | FUNC | [ ] |
| KAN-003 | Drag lead between columns | P1 | FUNC | [ ] |
| KAN-004 | Status updates after drag | P1 | FUNC | [ ] |
| KAN-005 | Open lead details on click | P1 | FUNC | [ ] |
| KAN-006 | Analytics view shows conversion rates | P2 | FUNC | [ ] |

### Conversion

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| KAN-007 | Convert lead to pupil | P1 | FUNC | [ ] |
| KAN-008 | Conversion form loads tariffs | P1 | FUNC | [ ] |
| KAN-009 | Groups filter by selected tariff | P2 | FUNC | [ ] |
| KAN-010 | Sales script displays during call | P2 | FUNC | [ ] |

### UI Tests

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| KAN-UI-001 | Columns scroll independently | P2 | UI | [ ] |
| KAN-UI-002 | Drag visual feedback | P2 | UI | [ ] |
| KAN-UI-003 | Mobile swipe between columns | P3 | UI | [ ] |

---

## 7. Leads Interaction (LidsInteractionController)

**URL:** `/<oid>/lids-interaction/`
**Actions:** 8 (add-interaction, convert-to-pupil, toggle-tag, update-field, get-whatsapp-templates, render-whatsapp-message, check-duplicates)

### Interactions

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| INT-001 | Add call interaction | P1 | FUNC | [ ] |
| INT-002 | Add note interaction | P1 | FUNC | [ ] |
| INT-003 | Add meeting interaction | P2 | FUNC | [ ] |
| INT-004 | Interaction history displays | P1 | FUNC | [ ] |
| INT-005 | Toggle tag on lead | P2 | FUNC | [ ] |
| INT-006 | Update lead field inline | P2 | FUNC | [ ] |
| INT-007 | Check for duplicate leads | P2 | FUNC | [ ] |

### WhatsApp Integration

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| INT-WA-001 | Load WhatsApp templates | P2 | FUNC | [ ] |
| INT-WA-002 | Render message with placeholders | P2 | FUNC | [ ] |

---

## 8. Lead Tags (LidTagController)

**URL:** `/<oid>/lid-tag/`
**Actions:** 6 (list, create, update, delete, toggle, get-lid-tags)

### CRUD Operations

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| TAG-001 | View list of tags | P2 | FUNC | [ ] |
| TAG-002 | Create new tag | P2 | FUNC | [ ] |
| TAG-003 | Set tag color | P3 | FUNC | [ ] |
| TAG-004 | Update tag | P2 | FUNC | [ ] |
| TAG-005 | Delete tag | P2 | FUNC | [ ] |
| TAG-006 | Toggle tag on lead | P2 | FUNC | [ ] |

---

## 9. Sales Scripts (SalesScriptController)

**URL:** `/<oid>/sales-script/`
**Actions:** 5 (index, create, update, delete, toggle)

### CRUD Operations

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| SCRIPT-001 | View sales scripts list | P2 | FUNC | [ ] |
| SCRIPT-002 | Create new script | P2 | FUNC | [ ] |
| SCRIPT-003 | Update script | P2 | FUNC | [ ] |
| SCRIPT-004 | Delete script | P2 | FUNC | [ ] |
| SCRIPT-005 | Toggle script active/inactive | P2 | FUNC | [ ] |

---

## 10. Payments (PaymentController)

**URL:** `/<oid>/payment/`
**Actions:** 14 (index, view, create, update, delete, request-delete, request-update, my-requests, pending-requests, view-request, approve-request, reject-request, receipt)

### CRUD Operations

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| PAY-001 | View payments list | P1 | FUNC | [ ] |
| PAY-002 | Filter by date range | P1 | FUNC | [ ] |
| PAY-003 | Filter by payment method | P2 | FUNC | [ ] |
| PAY-004 | Create payment | P1 | FUNC | [ ] |
| PAY-005 | Update payment | P1 | FUNC | [ ] |
| PAY-006 | Delete payment | P1 | FUNC | [ ] |
| PAY-007 | View payment details | P2 | FUNC | [ ] |
| PAY-008 | Print receipt | P2 | FUNC | [ ] |

### Request System

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| PAY-REQ-001 | Request payment deletion | P2 | FUNC | [ ] |
| PAY-REQ-002 | Request payment update | P2 | FUNC | [ ] |
| PAY-REQ-003 | View my requests | P2 | FUNC | [ ] |
| PAY-REQ-004 | View pending requests (admin) | P2 | FUNC | [ ] |
| PAY-REQ-005 | Approve request | P2 | FUNC | [ ] |
| PAY-REQ-006 | Reject request | P2 | FUNC | [ ] |

---

## 11. Tariffs (TariffController)

**URL:** `/<oid>/tariff/`
**Actions:** 6 (index, view, create, update, delete, get-info)

### CRUD Operations

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| TAR-001 | View tariffs list | P1 | FUNC | [ ] |
| TAR-002 | Create new tariff | P1 | FUNC | [ ] |
| TAR-003 | Set tariff price | P1 | FUNC | [ ] |
| TAR-004 | Set tariff lesson count | P1 | FUNC | [ ] |
| TAR-005 | Link tariff to subjects | P2 | FUNC | [ ] |
| TAR-006 | Update tariff | P1 | FUNC | [ ] |
| TAR-007 | Delete tariff | P2 | FUNC | [ ] |
| TAR-008 | Get tariff info (AJAX) | P2 | FUNC | [ ] |

---

## 12. Subjects (SubjectController)

**URL:** `/<oid>/subject/`
**Actions:** 5 (index, view, create, update, delete)

### CRUD Operations

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| SUB-001 | View subjects list | P1 | FUNC | [ ] |
| SUB-002 | Create new subject | P1 | FUNC | [ ] |
| SUB-003 | Update subject | P1 | FUNC | [ ] |
| SUB-004 | Delete subject | P2 | FUNC | [ ] |
| SUB-005 | Set subject color | P3 | FUNC | [ ] |

---

## 13. Users (UserController)

**URL:** `/<oid>/user/`
**Actions:** 6 (index, view, create, update, delete, reset-password)

### CRUD Operations

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| USR-001 | View users list | P1 | FUNC | [ ] |
| USR-002 | Create new user | P1 | FUNC | [ ] |
| USR-003 | Set user role | P1 | FUNC | [ ] |
| USR-004 | Update user | P1 | FUNC | [ ] |
| USR-005 | Delete user | P1 | FUNC | [ ] |
| USR-006 | Reset user password | P1 | FUNC | [ ] |
| USR-007 | Email validation | P1 | FUNC | [ ] |
| USR-008 | Password strength validation | P2 | FUNC | [ ] |

---

## 14. Salaries (SalaryController)

**URL:** `/<oid>/salary/`
**Actions:** 13 (index, view, calculate, update, recalculate, approve, pay, delete, rates, create-rate, update-rate, delete-rate)

### Salary Management

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| SAL-001 | View salary records | P1 | FUNC | [ ] |
| SAL-002 | Calculate monthly salaries | P1 | FUNC | [ ] |
| SAL-003 | View salary details | P1 | FUNC | [ ] |
| SAL-004 | Update salary record | P2 | FUNC | [ ] |
| SAL-005 | Recalculate salary | P2 | FUNC | [ ] |
| SAL-006 | Approve salary | P1 | FUNC | [ ] |
| SAL-007 | Mark salary as paid | P1 | FUNC | [ ] |
| SAL-008 | Delete salary record | P2 | FUNC | [ ] |

### Salary Rates

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| SAL-RATE-001 | View salary rates | P1 | FUNC | [ ] |
| SAL-RATE-002 | Create salary rate | P1 | FUNC | [ ] |
| SAL-RATE-003 | Update salary rate | P2 | FUNC | [ ] |
| SAL-RATE-004 | Delete salary rate | P2 | FUNC | [ ] |

---

## 15. Reports (ReportsController)

**URL:** `/<oid>/reports/`
**Actions:** 8 (index, category, view, export, employer, day, month, test)

### Report Generation

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| REP-001 | View reports dashboard | P1 | FUNC | [ ] |
| REP-002 | View report by category | P1 | FUNC | [ ] |
| REP-003 | View specific report | P1 | FUNC | [ ] |
| REP-004 | Export to Excel | P1 | FUNC | [ ] |
| REP-005 | Employer report | P2 | FUNC | [ ] |
| REP-006 | Daily report | P2 | FUNC | [ ] |
| REP-007 | Monthly report | P2 | FUNC | [ ] |
| REP-008 | Date range filter works | P1 | FUNC | [ ] |

---

## 16. Attendance (AttendanceController)

**URL:** `/<oid>/attendance/`
**Actions:** 2 (index, lesson)

### Attendance Tracking

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| ATT-001 | View attendance page | P1 | FUNC | [ ] |
| ATT-002 | Mark attendance for lesson | P1 | FUNC | [ ] |
| ATT-003 | Mark present | P1 | FUNC | [ ] |
| ATT-004 | Mark absent | P1 | FUNC | [ ] |
| ATT-005 | Mark late | P2 | FUNC | [ ] |
| ATT-006 | Add attendance notes | P3 | FUNC | [ ] |
| ATT-007 | Attendance persists after page reload | P1 | FUNC | [ ] |

---

## 17. Rooms (RoomController)

**URL:** `/<oid>/room/`
**Actions:** 5 (index, view, create, update, delete)

### CRUD Operations

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| ROOM-001 | View rooms list | P1 | FUNC | [ ] |
| ROOM-002 | Create new room | P1 | FUNC | [ ] |
| ROOM-003 | Set room capacity | P2 | FUNC | [ ] |
| ROOM-004 | Update room | P1 | FUNC | [ ] |
| ROOM-005 | Delete room | P2 | FUNC | [ ] |

---

## 18. Pay Methods (PayMethodController)

**URL:** `/<oid>/pay-method/`
**Actions:** 5 (index, view, create, update, delete)

### CRUD Operations

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| PM-001 | View payment methods list | P1 | FUNC | [ ] |
| PM-002 | Create payment method (Cash) | P1 | FUNC | [ ] |
| PM-003 | Create payment method (Card) | P1 | FUNC | [ ] |
| PM-004 | Create payment method (Transfer) | P2 | FUNC | [ ] |
| PM-005 | Update payment method | P1 | FUNC | [ ] |
| PM-006 | Delete payment method | P2 | FUNC | [ ] |

---

## 19. Schedule Templates (ScheduleTemplateController)

**URL:** `/<oid>/schedule-template/`
**Actions:** 14 (index, view, create, update, delete, duplicate, events, add-lesson, update-lesson, delete-lesson, preview, generate, form-data, create-from-schedule)

### Template Management

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| TPL-001 | View templates list | P2 | FUNC | [ ] |
| TPL-002 | Create new template | P2 | FUNC | [ ] |
| TPL-003 | Add lessons to template | P2 | FUNC | [ ] |
| TPL-004 | Update template | P2 | FUNC | [ ] |
| TPL-005 | Delete template | P3 | FUNC | [ ] |
| TPL-006 | Duplicate template | P3 | FUNC | [ ] |
| TPL-007 | Preview template | P2 | FUNC | [ ] |
| TPL-008 | Generate schedule from template | P1 | FUNC | [ ] |
| TPL-009 | Create template from existing schedule | P3 | FUNC | [ ] |

---

## 20. Settings (SettingsController)

**URL:** `/<oid>/settings/`
**Actions:** 3 (access, ajax-save-setting, index)

### Settings Management

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| SET-001 | View settings page | P1 | FUNC | [ ] |
| SET-002 | Access permissions settings | P1 | FUNC | [ ] |
| SET-003 | Save setting via AJAX | P1 | FUNC | [ ] |
| SET-004 | Settings persist after page reload | P1 | FUNC | [ ] |
| SET-005 | Toggle settings work correctly | P2 | FUNC | [ ] |

---

## 21. SMS (SmsController)

**URL:** `/<oid>/sms/`
**Actions:** 9 (index, templates, create-template, update-template, delete-template, create-defaults, settings, save-notification-setting, test-send)

### SMS Management

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| SMS-001 | View SMS dashboard | P1 | FUNC | [ ] |
| SMS-002 | View SMS templates | P1 | FUNC | [ ] |
| SMS-003 | Create SMS template | P1 | FUNC | [ ] |
| SMS-004 | Update SMS template | P1 | FUNC | [ ] |
| SMS-005 | Delete SMS template | P2 | FUNC | [ ] |
| SMS-006 | Create default templates | P2 | FUNC | [ ] |
| SMS-007 | Configure SMS settings | P1 | FUNC | [ ] |
| SMS-008 | Save notification settings | P1 | FUNC | [ ] |
| SMS-009 | Test send SMS | P1 | FUNC | [ ] |
| SMS-010 | Template placeholders work | P1 | FUNC | [ ] |

---

## 22. WhatsApp (WhatsappController)

**URL:** `/<oid>/whatsapp/`
**Actions:** 19 (index, connect, disconnect, get-qr-code, get-status, chats, chat, get-chat-content, load-more-messages, send-message, send-media, get-messages, mark-read, link-to-lid, create-lid-from-chat, webhook, download-media, reconfigure-webhook, widget-chats)

### Connection

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| WA-001 | View WhatsApp page | P1 | FUNC | [ ] |
| WA-002 | Connect WhatsApp (QR code) | P1 | FUNC | [ ] |
| WA-003 | Disconnect WhatsApp | P1 | FUNC | [ ] |
| WA-004 | Get connection status | P1 | FUNC | [ ] |

### Chat Management

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| WA-005 | View chats list | P1 | FUNC | [ ] |
| WA-006 | Open chat conversation | P1 | FUNC | [ ] |
| WA-007 | Load chat messages | P1 | FUNC | [ ] |
| WA-008 | Load more messages (pagination) | P2 | FUNC | [ ] |
| WA-009 | Send text message | P1 | FUNC | [ ] |
| WA-010 | Send media (image/file) | P2 | FUNC | [ ] |
| WA-011 | Mark messages as read | P2 | FUNC | [ ] |
| WA-012 | Download media from message | P2 | FUNC | [ ] |

### Lead Integration

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| WA-013 | Link chat to existing lead | P2 | FUNC | [ ] |
| WA-014 | Create lead from chat | P2 | FUNC | [ ] |
| WA-015 | Widget shows recent chats | P2 | FUNC | [ ] |

### UI Tests

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| WA-UI-001 | Chat list scrolls correctly | P2 | UI | [ ] |
| WA-UI-002 | Messages display in correct order | P1 | UI | [ ] |
| WA-UI-003 | Media thumbnails display | P2 | UI | [ ] |
| WA-UI-004 | Unread badge shows count | P2 | UI | [ ] |

---

## 23. Notifications (NotificationController)

**URL:** `/<oid>/notification/`
**Actions:** 6 (get-notifications, get-unread-count, mark-read, mark-all-read, index, create-lid-reminder)

### Notification Management

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| NOT-001 | Get notifications list | P1 | FUNC | [ ] |
| NOT-002 | Get unread count | P1 | FUNC | [ ] |
| NOT-003 | Mark notification as read | P1 | FUNC | [ ] |
| NOT-004 | Mark all as read | P2 | FUNC | [ ] |
| NOT-005 | Create lead reminder | P2 | FUNC | [ ] |
| NOT-006 | Notification bell shows count | P1 | FUNC | [ ] |

---

## 24. Subscription (SubscriptionController)

**URL:** `/<oid>/subscription/`
**Actions:** 14 (index, plans, renew, upgrade, payments, usage, blocked, request-renewal, start-trial, trials, cancel-trial, convert-trial, request-upgrade, requests)

### Subscription Management

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| SUBS-001 | View current subscription | P1 | FUNC | [ ] |
| SUBS-002 | View available plans | P1 | FUNC | [ ] |
| SUBS-003 | Request subscription renewal | P1 | FUNC | [ ] |
| SUBS-004 | Request upgrade | P2 | FUNC | [ ] |
| SUBS-005 | View payment history | P2 | FUNC | [ ] |
| SUBS-006 | View usage statistics | P2 | FUNC | [ ] |
| SUBS-007 | Blocked page displays correctly | P1 | FUNC | [ ] |

### Trial Management

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| SUBS-TRIAL-001 | Start trial | P2 | FUNC | [ ] |
| SUBS-TRIAL-002 | View active trials | P2 | FUNC | [ ] |
| SUBS-TRIAL-003 | Cancel trial | P2 | FUNC | [ ] |
| SUBS-TRIAL-004 | Convert trial to subscription | P2 | FUNC | [ ] |

---

## 25. Knowledge Base (KnowledgeController)

**URL:** `/<oid>/knowledge/`
**Actions:** 4 (index, category, view, search)

### Knowledge Base

| ID | Test | Priority | Type | Status |
|----|------|----------|------|--------|
| KB-001 | View knowledge base index | P2 | FUNC | [ ] |
| KB-002 | View category articles | P2 | FUNC | [ ] |
| KB-003 | View single article | P2 | FUNC | [ ] |
| KB-004 | Search articles | P2 | FUNC | [ ] |

---

## Spelling Verification

### Russian Text Verification

Check the following common spelling issues in Russian text:

| ID | Area | Check | Status |
|----|------|-------|--------|
| SPELL-001 | Menu items | "Ученики" (not "Учиники") | [ ] |
| SPELL-002 | Menu items | "Расписание" (not "Расписанние") | [ ] |
| SPELL-003 | Menu items | "Настройки" (not "Настроики") | [ ] |
| SPELL-004 | Button labels | "Сохранить" (not "Сохронить") | [ ] |
| SPELL-005 | Button labels | "Отменить" (not "Отминить") | [ ] |
| SPELL-006 | Button labels | "Добавить" (not "Добавит") | [ ] |
| SPELL-007 | Form labels | "Телефон" (not "Тилефон") | [ ] |
| SPELL-008 | Form labels | "Электронная почта" | [ ] |
| SPELL-009 | Error messages | "Обязательное поле" | [ ] |
| SPELL-010 | Success messages | "Успешно сохранено" | [ ] |
| SPELL-011 | Page titles | "Добавить ученика" | [ ] |
| SPELL-012 | Page titles | "Редактирование" | [ ] |
| SPELL-013 | Tooltips | Check all tooltip texts | [ ] |
| SPELL-014 | Placeholders | Check all placeholder texts | [ ] |
| SPELL-015 | Validation | "Минимум N символов" | [ ] |

### Kazakh Text Verification (if enabled)

| ID | Area | Check | Status |
|----|------|-------|--------|
| SPELL-KZ-001 | Menu items | Check Kazakh translations | [ ] |
| SPELL-KZ-002 | Form labels | Check Kazakh translations | [ ] |
| SPELL-KZ-003 | Messages | Check Kazakh translations | [ ] |

---

## UI/UX Checklist

### Forms

| ID | Check | Status |
|----|-------|--------|
| UX-FORM-001 | Required fields marked with asterisk (*) | [ ] |
| UX-FORM-002 | Form validation errors appear near fields | [ ] |
| UX-FORM-003 | Error messages are red colored | [ ] |
| UX-FORM-004 | Success messages are green colored | [ ] |
| UX-FORM-005 | Submit button shows loading state | [ ] |
| UX-FORM-006 | Tab order is logical | [ ] |
| UX-FORM-007 | Enter key submits form | [ ] |
| UX-FORM-008 | Cancel button is clearly visible | [ ] |

### Tables

| ID | Check | Status |
|----|-------|--------|
| UX-TABLE-001 | Tables are responsive (scroll on mobile) | [ ] |
| UX-TABLE-002 | Column headers are clear | [ ] |
| UX-TABLE-003 | Sort indicators visible | [ ] |
| UX-TABLE-004 | Empty state message shown | [ ] |
| UX-TABLE-005 | Action buttons visible and accessible | [ ] |
| UX-TABLE-006 | Pagination controls work | [ ] |

### Modals

| ID | Check | Status |
|----|-------|--------|
| UX-MODAL-001 | Modals can be closed with X button | [ ] |
| UX-MODAL-002 | Modals can be closed with Escape key | [ ] |
| UX-MODAL-003 | Clicking overlay closes modal | [ ] |
| UX-MODAL-004 | Modal content is scrollable if needed | [ ] |
| UX-MODAL-005 | Focus trapped inside modal | [ ] |

### Navigation

| ID | Check | Status |
|----|-------|--------|
| UX-NAV-001 | Current page highlighted in menu | [ ] |
| UX-NAV-002 | Breadcrumbs show current path | [ ] |
| UX-NAV-003 | Back buttons work correctly | [ ] |
| UX-NAV-004 | Mobile menu works | [ ] |
| UX-NAV-005 | Submenus open correctly | [ ] |

### Buttons

| ID | Check | Status |
|----|-------|--------|
| UX-BTN-001 | Primary action button is highlighted | [ ] |
| UX-BTN-002 | Destructive actions are red | [ ] |
| UX-BTN-003 | Buttons have hover states | [ ] |
| UX-BTN-004 | Disabled buttons look disabled | [ ] |
| UX-BTN-005 | Icons align with text | [ ] |

### Loading States

| ID | Check | Status |
|----|-------|--------|
| UX-LOAD-001 | Loading spinner during data fetch | [ ] |
| UX-LOAD-002 | Skeleton loaders for content areas | [ ] |
| UX-LOAD-003 | Button spinner during submit | [ ] |
| UX-LOAD-004 | Page loading indicator | [ ] |

### Colors & Consistency

| ID | Check | Status |
|----|-------|--------|
| UX-COLOR-001 | Status colors consistent (green=success, red=error) | [ ] |
| UX-COLOR-002 | Links are distinguishable from text | [ ] |
| UX-COLOR-003 | Font sizes consistent across pages | [ ] |
| UX-COLOR-004 | Spacing consistent across components | [ ] |

---

## RBAC Tests

### Role: GENERAL_DIRECTOR (Highest access)

| ID | Check | Status |
|----|-------|--------|
| RBAC-GD-001 | Can access all menu items | [ ] |
| RBAC-GD-002 | Can view all data | [ ] |
| RBAC-GD-003 | Can create/edit/delete all entities | [ ] |
| RBAC-GD-004 | Can access settings | [ ] |
| RBAC-GD-005 | Can manage users | [ ] |
| RBAC-GD-006 | Can approve payment requests | [ ] |
| RBAC-GD-007 | Can access reports | [ ] |

### Role: DIRECTOR

| ID | Check | Status |
|----|-------|--------|
| RBAC-DIR-001 | Can access most menu items | [ ] |
| RBAC-DIR-002 | Can manage pupils | [ ] |
| RBAC-DIR-003 | Can manage groups | [ ] |
| RBAC-DIR-004 | Can manage teachers | [ ] |
| RBAC-DIR-005 | Can view reports | [ ] |
| RBAC-DIR-006 | Cannot access subscription settings | [ ] |

### Role: ADMIN

| ID | Check | Status |
|----|-------|--------|
| RBAC-ADM-001 | Can manage pupils | [ ] |
| RBAC-ADM-002 | Can manage leads | [ ] |
| RBAC-ADM-003 | Can manage payments | [ ] |
| RBAC-ADM-004 | Limited settings access | [ ] |
| RBAC-ADM-005 | Cannot delete users | [ ] |

### Role: TEACHER

| ID | Check | Status |
|----|-------|--------|
| RBAC-TCH-001 | Can view own groups only | [ ] |
| RBAC-TCH-002 | Can mark attendance | [ ] |
| RBAC-TCH-003 | Cannot manage payments | [ ] |
| RBAC-TCH-004 | Cannot access admin settings | [ ] |
| RBAC-TCH-005 | Can view own schedule | [ ] |
| RBAC-TCH-006 | Limited menu access | [ ] |

---

## Test Execution Log

### Session Template

```markdown
## Test Session: [DATE]

**Tester:** [NAME]
**Environment:** [Production/Staging/Local]
**Browser:** [Chrome/Firefox/Safari/Edge]
**Organization ID:** [OID]

### Tests Executed
- [ ] Category: [X passed / Y failed]

### Issues Found
1. [Issue ID] - [Description]

### Notes
- [Any observations]
```

---

## Known Issues Log

| ID | Date Found | Description | Severity | Status |
|----|------------|-------------|----------|--------|
| | | | | |

---

## Appendix: Test Data Requirements

### Minimum Test Data

1. **Organization:** At least 1 active organization
2. **Users:**
   - 1 General Director
   - 1 Admin
   - 1 Teacher
3. **Subjects:** At least 2 subjects
4. **Tariffs:** At least 2 tariffs
5. **Groups:** At least 3 groups
6. **Pupils:** At least 10 pupils
7. **Leads:** At least 5 leads in different statuses
8. **Payments:** At least 5 payment records
9. **Rooms:** At least 2 rooms
10. **Pay Methods:** At least 2 payment methods (cash, card)

### Test User Credentials Template

| Role | Username | Password |
|------|----------|----------|
| General Director | | |
| Director | | |
| Admin | | |
| Teacher | | |
