# PRD: Kokurikuler Kelas 10 Management App (SMK Wikrama Bogor)

| Item | Value |
| --- | --- |
| Product | Kokurikuler Kelas 10 Management App |
| Owner | Shofia Kamilah Wahyudi (PPLG XI-3) |
| Platform | Web app, Laravel 13, Blade, MySQL |
| UI language | Indonesian (all user-facing text) |
| Code language | English (tables, columns, classes, variables, routes) |
| Status | Draft for design and build handoff |

This document describes what to build and why. The companion file `PROMPT_Build_Kokurikuler_Laravel.md` tells an AI coding assistant how to build it.

---

## 1. Overview

Grade 10 students at SMK Wikrama run a daily "kokurikuler" in rotation. Each day 1 to 2 classrooms (rombel) are on duty. Their students are assigned to posts: floor cleaning, toilet monitoring, canteens, plus a separate pool of students who do toilet duty (piket) on a given day. Cleaning officers also handle waste sorting, planting, and the Wednesday collection of used cooking oil and eggshells.

Everything is recorded on paper today. This app replaces the paper with digital records, shows each student their own tasks, and keeps archives that can be exported to Excel for the end-of-semester report.

## 2. Problems to solve

1. Officers cannot see at a glance which rooms already delivered their trash bin.
2. The Wednesday collection queue is slow because names are written by hand.
3. End-of-semester reports are hard to compile from scattered paper.
4. Task assignments change every cycle, so access and reminders are error-prone.
5. Students do not reliably know when they are assigned (especially toilet duty).

## 3. Goals and non-goals

### 3.1 Goals

| ID | Goal | Success measure |
| --- | --- | --- |
| G1 | Teacher defines all student assignments in one spreadsheet upload | A full cycle uploads and validates in one pass |
| G2 | Students see their own tasks and get in-app reminders | Every assigned student sees a banner on the day before and on the day of the task |
| G3 | Officers record everything on a phone | Floor map, sorted waste, activities, collection, sales, and attendance all work on a 360 px wide screen |
| G4 | Faster Wednesday collection | Recording one student's contribution takes under 30 seconds |
| G5 | Reports without manual compiling | Archive export to Excel in at most 3 clicks |

### 3.2 Non-goals (do not build)

- Any ranking, leaderboard, "most diligent" or compliance score, for students, classrooms, or rooms. The school uses moving classes, so every room is used by different classrooms during the day and such rankings would be unfair.
- Student self-submission of contributions, submission codes, or confirmation codes.
- An automatic 14-day schedule generator or automatic rotation. The teacher defines assignments through the spreadsheet.
- Plant tracking, teacher verification of records, selling used oil with stock, trend charts, audit trail, WhatsApp or email notifications.

## 4. Users and roles

Two stored roles: `teacher` and `student`. Duty status is **not stored on the account**. It is derived every day from the student's assignment for that date.

| Actor | How determined | What they do |
| --- | --- | --- |
| Visitor | Not logged in | Sees the public home page |
| Teacher | `role = teacher` | Manages assignments, reads all archives, exports |
| Cleaning officer | Student with a `cleaning` assignment today | Floor map, sorted waste, activity log, Wednesday collection, attendance |
| Toilet officer | Student with a `toilet_officer` assignment today | Records toilet duty attendance for their floor, adds replacements |
| Canteen officer | Student with a `canteen` assignment today | Records canteen sales for their canteen, attendance |
| Toilet duty student | Student with a `toilet_duty` assignment | Sees reminders and "My Tasks". Any student can have this (including grades 11 and 12) |
| Regular student | No assignment today | Sees home, My Tasks, own classroom archive |

"Officer" means a student whose task type today is `cleaning`, `toilet_officer`, or `canteen`. A `toilet_duty` student is not an officer.

## 5. Core concepts

**Assignment.** One row per student per date with a task type and a location.

| Task type key | Indonesian label (spreadsheet and UI) | Location |
| --- | --- | --- |
| `cleaning` | Kebersihan | A floor (for example Lantai 2) |
| `toilet_officer` | Petugas toilet | A floor |
| `toilet_duty` | Piket toilet | A floor |
| `canteen` | Kantin | A canteen: Kantin Utama, IDS, Cafe Hotel, BDP |

**Floor and room.** A floor has rooms drawn on an interactive map. Each room is a clickable box.

**Semester.** Used only to label and filter archives and to show the current semester.

**On duty today.** A student is on duty today when they have an assignment with `assignment_date = today` (timezone Asia/Jakarta).

## 6. Functional requirements

Priority: **P1** = must have in the first release. **P2** = nice to have, only after P1 is accepted.

### 6.1 Public home (P1)

- FR-HOME-1: Show the date and the current semester label if one exists.
- FR-HOME-2: Show "On duty today": for each classroom with a duty assignment today (task types `cleaning`, `toilet_officer`, `canteen`), show the classroom name and the number of students per task and location. Do not show student names or NIS on this public page.
- FR-HOME-3: Show four impact totals: cooking oil (liters), eggshells (kg), sorted waste (kg, organic plus non-organic plus residue), and number of activity logs.
- FR-HOME-4: If no classroom is on duty, show a friendly empty message.
- FR-HOME-5: If a logged-in student has an assignment today or on the next weekday, show a prominent reminder banner (see 6.4).
- FR-HOME-6: No ranking or comparison between classrooms.

### 6.2 Authentication and access (P1)

- FR-AUTH-1: Students register with name, NIS, classroom, attendance number, email, password, and password confirmation. Role is set to `student` automatically. After registering the student is logged in and sent to the home page.
- FR-AUTH-2: Login with email and password. After login: a teacher goes to the teacher dashboard, an officer goes to the duty dashboard, any other student goes to the home page.
- FR-AUTH-3: Logout is a POST action.
- FR-AUTH-4: The teacher account is created by a seeder. There is no teacher registration.
- FR-AUTH-5: Wrong access returns a friendly 403 page.
- FR-AUTH-6: Passwords are always stored hashed.

### 6.3 Teacher: assignments (P1)

- FR-ASG-1: Assignment list with filters for date (default today) and task type. Columns: date, student name, NIS, classroom, task, location. Each row has a delete action with confirmation.
- FR-ASG-2: Add a single assignment: date, NIS, task type, location. If an assignment already exists for that student and date, it is updated.
- FR-ASG-3: Upload assignments from a CSV file (see section 9), with a preview step before saving.
- FR-ASG-4: Download a CSV template with the header row and example rows.
- FR-ASG-5: Teacher dashboard cards: classrooms on duty today (of a maximum of 2), officers marked present today out of officers assigned, rooms delivered today out of total rooms, and Wednesday collection entries today. Below the cards, a table of today's assignments.
- FR-ASG-6 (P2): A "Student data" page where the teacher can correct a student's classroom and attendance number.

### 6.4 Student: My Tasks and reminders (P1)

- FR-TASK-1: "My Tasks" page for every student listing upcoming assignments (today and later) with date, task, and location.
- FR-TASK-2: Reminder banner on the home page and the My Tasks page when the student has an assignment today or on the next weekday. Banner text includes the task, location, and date.
- FR-TASK-3: Reminders live inside the app only.

### 6.5 Officer dashboard "Today's Duty" (P1)

- FR-DUTY-1: Shows the student's task and location today.
- FR-DUTY-2: Cleaning officer sees cards that link to Floor Map (with delivered/total), Sorted Waste, Activity Log, Wednesday Collection, and Attendance.
- FR-DUTY-3: Toilet officer sees a card linking to Toilet Duty (count of students listed for their floor) and Attendance.
- FR-DUTY-4: Canteen officer sees their canteen, today's sales total, and links to Canteen Sales and Attendance.
- FR-DUTY-5: A student without an officer assignment today is redirected to the home page with a short message.

### 6.6 Floor map and trash bin delivery (P1, cleaning officer)

- FR-MAP-1: Floor tabs show progress per floor (example 3/8 delivered).
- FR-MAP-2: The selected floor shows its rooms as boxes positioned by percentage values, plus a corridor strip.
- FR-MAP-3: Tapping a room marks its trash bin as delivered today (green with a check icon and text). Tapping again undoes it.
- FR-MAP-4: Status is per date. The map starts empty each day.
- FR-MAP-5: Status is never conveyed by color alone.

### 6.7 Sorted waste (P1, cleaning officer)

- FR-WASTE-1: A form with organic, non-organic, and residue weights in kilograms (decimal, 0 to 999).
- FR-WASTE-2: One record per classroom per date. Saving again on the same date updates it.
- FR-WASTE-3: Shows today's record for the officer's classroom.

### 6.8 Activity log (P1, cleaning officer)

- FR-ACT-1: Form: activity type (`cleaning`, `planting`, `toilet_duty`, `sorting`), floor (optional), description (optional, up to 500 characters), photo (optional, image, up to 4 MB).
- FR-ACT-2: List of today's logs for the officer's classroom with type, floor, recorder, time, description, and a photo link.

### 6.9 Wednesday collection (P1, cleaning officer)

- FR-COL-1: The officer types at least 3 digits of an NIS. The app shows up to 5 matching students (name, NIS, classroom).
- FR-COL-2: The officer selects a student, chooses the waste type (`cooking_oil` in liters or `eggshell` in kg), enters the amount (0.01 to 999), and saves.
- FR-COL-3: A list of today's entries shows contributor, classroom, type, amount.
- FR-COL-4: Students never enter their own contributions, and there are no codes.
- FR-COL-5: The page is intended for Wednesdays but is not blocked on other days (see open question OQ-1).

### 6.10 Canteen sales (P1, canteen officer)

- FR-SALE-1: Form: item name, quantity, unit price (rupiah).
- FR-SALE-2: Table of today's sales for the officer's canteen with line totals and a day total.
- FR-SALE-3: Delete is allowed only for entries recorded by the officer's own classroom on the same day.

### 6.11 Attendance (P1, any officer)

- FR-ATT-1: Cleaning and toilet officers see all today's `cleaning` and `toilet_officer` assignments of their own classroom. Canteen officers see all today's `canteen` assignments of their own classroom.
- FR-ATT-2: Each row has a status (`present`, `permitted`, `sick`, `absent`, labels Hadir, Izin, Sakit, Alpa) and an optional note (up to 200 characters). The default shown is `present`.
- FR-ATT-3: One attendance record per assignment. Saving again updates it.
- FR-ATT-4: The teacher reads attendance in the archive.

### 6.12 Toilet duty recording (P1, toilet officer)

- FR-TOI-1: Lists students with a `toilet_duty` assignment today on the officer's floor, with the student's classroom.
- FR-TOI-2: For each student the officer records `present` or `absent` and an optional note (up to 200 characters).
- FR-TOI-3: The officer can add a replacement student by NIS search. This creates a `toilet_duty` assignment for today on the officer's floor, with the officer as `created_by`. Rejected if that student already has an assignment today.
- FR-TOI-4: Toilet duty attendance is stored in the same attendance table and appears in the archive.

### 6.13 Archive and export (P1)

- FR-ARC-1: Seven data types: activity logs, trash bin deliveries, sorted waste, Wednesday collections, canteen sales, attendance (all task types, including toilet duty), assignments.
- FR-ARC-2: Filters: data type, semester, and classroom (teacher only). Default semester: the current one, or the latest if none is current.
- FR-ARC-3: Teacher sees all classrooms. A student sees only their own classroom.
- FR-ARC-4: Export downloads a CSV for the current filters. Format: UTF-8 with BOM, semicolon delimiter, so Excel opens it with proper columns.
- FR-ARC-5: Wide tables scroll horizontally on phones.

## 7. Business rules

| ID | Rule |
| --- | --- |
| BR-1 | Duty status comes from today's assignment. It is never stored on the account. |
| BR-2 | One assignment per student per date (unique `user_id` + `assignment_date`). |
| BR-3 | For duty tasks (`cleaning`, `toilet_officer`, `canteen`) at most 2 distinct classrooms may be assigned on the same date. `toilet_duty` does not count toward this limit. |
| BR-4 | Duty tasks (`cleaning`, `toilet_officer`, `canteen`) may only be assigned to grade 10 students. `toilet_duty` may be assigned to any student. |
| BR-5 | Assignment dates must be weekdays (Monday to Friday). |
| BR-6 | Locations must match the task: `cleaning`, `toilet_officer`, `toilet_duty` need a floor. `canteen` needs a canteen. |
| BR-7 | A room can be marked delivered once per date. Marking again removes the mark. |
| BR-8 | One sorted waste record per classroom per date. |
| BR-9 | Activity logs, deliveries, collections, and sales are always recorded for today. |
| BR-10 | Toilet duty attendance accepts only `present` or `absent`. |
| BR-11 | Reminder window: assignments dated today or on the next weekday (Friday looks ahead to Monday). |
| BR-12 | No ranking or scoring of students, classrooms, rooms, or canteens anywhere. |
| BR-13 | The public page shows no student names or NIS. |

## 8. Data model

All tables also have `created_at` and `updated_at`. Types are MySQL via Laravel migrations.

### 8.1 `users` (extend the default Laravel table)

| Column | Type | Notes |
| --- | --- | --- |
| id | bigint, PK | |
| name | string(255) | |
| email | string, unique | |
| password | string | hashed |
| role | string, default `student` | `teacher` or `student` |
| nis | string(20), unique, nullable | teachers have none |
| classroom_id | FK classrooms, nullable, null on delete | |
| attendance_number | unsigned tinyint, nullable | 1 to 40 |

### 8.2 `classrooms`

| Column | Type | Notes |
| --- | --- | --- |
| id | PK | |
| name | string, unique | example `PPLG X-1` |
| grade_level | unsigned tinyint | 10, 11, or 12 |

### 8.3 `semesters`

| Column | Type | Notes |
| --- | --- | --- |
| id | PK | |
| academic_year | string | example `2026/2027` |
| semester_number | unsigned tinyint | 1 or 2 |
| start_date | date | |
| end_date | date | |

### 8.4 `floors` and `rooms`

`floors`: id, name (string, example `Lantai 1`), level (unsigned tinyint).

`rooms`: id, floor_id (FK floors, cascade), name (string), pos_x, pos_y, width, height (unsigned tinyint, percent of the map).

### 8.5 `canteens`

id, name (string, unique): Kantin Utama, IDS, Cafe Hotel, BDP.

### 8.6 `duty_assignments`

| Column | Type | Notes |
| --- | --- | --- |
| id | PK | |
| user_id | FK users, cascade | the assigned student |
| assignment_date | date | |
| task_type | string(20) | `cleaning`, `toilet_officer`, `toilet_duty`, `canteen` |
| floor_id | FK floors, nullable, null on delete | required for floor tasks |
| canteen_id | FK canteens, nullable, null on delete | required for `canteen` |
| created_by | FK users | teacher, or toilet officer for replacements |

Unique: (`user_id`, `assignment_date`).

### 8.7 `attendances`

| Column | Type | Notes |
| --- | --- | --- |
| id | PK | |
| duty_assignment_id | FK duty_assignments, cascade, unique | one record per assignment |
| status | string(10) | `present`, `permitted`, `sick`, `absent` |
| note | string(200), nullable | |
| recorded_by | FK users | |

### 8.8 `waste_deliveries`

id, room_id (FK rooms, cascade), user_id (FK users, cascade, the marking officer), delivery_date (date). Unique (`room_id`, `delivery_date`).

### 8.9 `activity_logs`

id, user_id (FK users, cascade), floor_id (FK floors, nullable, null on delete), activity_date (date), activity_type (string: `cleaning`, `planting`, `toilet_duty`, `sorting`), description (text, nullable), photo (string, nullable).

### 8.10 `sorted_wastes`

id, classroom_id (FK classrooms, cascade), user_id (FK users, cascade, the recorder), sorted_date (date), organic_weight, inorganic_weight, residue_weight (each decimal(8,2)). Unique (`classroom_id`, `sorted_date`).

### 8.11 `wednesday_collections`

id, contributor_id (FK users, cascade, the student who brought waste), user_id (FK users, cascade, the recording officer), collection_date (date), waste_type (string: `cooking_oil`, `eggshell`), amount (decimal(8,2)).

### 8.12 `canteen_sales`

id, user_id (FK users, cascade), canteen_id (FK canteens, cascade), sale_date (date), item_name (string(100)), quantity (unsigned integer), price (unsigned integer, rupiah per unit).

### 8.13 Relationships

- User belongs to Classroom. User has many DutyAssignments.
- DutyAssignment belongs to User, Floor (optional), Canteen (optional). It has one Attendance.
- Room belongs to Floor. Floor has many Rooms.
- WasteDelivery belongs to Room and User.
- ActivityLog belongs to User and Floor (optional).
- SortedWaste belongs to Classroom and User.
- WednesdayCollection belongs to contributor User and recorder User.
- CanteenSale belongs to User and Canteen.

## 9. Assignment CSV import

**Source:** the teacher fills a spreadsheet and saves it as CSV from Excel. A matching `.xlsx` template is provided separately. The app reads CSV only, so no extra package is needed.

**Columns (header row, in this order):** `Tanggal`, `NIS`, `Tugas`, `Lokasi`.

| Column | Accepts | Maps to |
| --- | --- | --- |
| Tanggal | `yyyy-mm-dd` or `dd/mm/yyyy` | `assignment_date` |
| NIS | text of the student's NIS | `user_id` |
| Tugas | `Kebersihan`, `Petugas toilet`, `Piket toilet`, `Kantin` (case-insensitive, trimmed) | `task_type` |
| Lokasi | a floor name for floor tasks, a canteen name for `Kantin` (case-insensitive, trimmed) | `floor_id` or `canteen_id` |

**File rules:** CSV or TXT, up to 2 MB, up to 2000 data rows. The delimiter is detected from the header line (`;` or `,`). A UTF-8 BOM is ignored. Blank lines are skipped.

**Flow:** upload, then preview, then confirm.

1. Upload validates the file type and size, stores it temporarily, and redirects to the preview.
2. Preview parses every row and shows its status: new, updated (an assignment exists for that student and date with different values), unchanged, or error with a message.
3. Confirm is available only when there are zero errors. Saving is all-or-nothing inside a database transaction. The temporary file is deleted afterwards.
4. If there are errors, the teacher fixes the file and uploads again. Nothing is saved.

**Row error messages (Indonesian):**

| Condition | Message |
| --- | --- |
| Invalid date | Tanggal tidak valid |
| Saturday or Sunday | Tanggal jatuh pada akhir pekan |
| NIS not found or not a student | NIS tidak terdaftar |
| Unknown task | Tugas tidak dikenal |
| Location not found | Lokasi tidak dikenal |
| Location type does not match task | Lokasi tidak sesuai dengan tugas |
| Duty task for a non grade 10 student | Tugas ini hanya untuk siswa kelas 10 |
| Same NIS twice on the same date in the file | NIS dobel pada tanggal yang sama |
| More than 2 classrooms on duty on that date | Lebih dari 2 rombel bertugas pada tanggal ini |

## 10. Permissions

| Feature | Teacher | Cleaning officer | Toilet officer | Canteen officer | Toilet duty student | Regular student |
| --- | --- | --- | --- | --- | --- | --- |
| Public home | Yes | Yes | Yes | Yes | Yes | Yes |
| Manage assignments | Yes | No | No | No | No | No |
| Teacher dashboard | Yes | No | No | No | No | No |
| My Tasks | No | Yes | Yes | Yes | Yes | Yes |
| Today's Duty dashboard | No | Yes | Yes | Yes | No | No |
| Floor map | No | Yes | No | No | No | No |
| Sorted waste | No | Yes | No | No | No | No |
| Activity log | No | Yes | No | No | No | No |
| Wednesday collection | No | Yes | No | No | No | No |
| Canteen sales | No | No | No | Yes | No | No |
| Attendance | Archive only | Yes | Yes | Yes | No | No |
| Toilet duty recording | Archive only | No | Yes | No | No | No |
| Archive and export | All classrooms | Own classroom | Own classroom | Own classroom | Own classroom | Own classroom |

"My Tasks" is for students only. A teacher has no assignments.

## 11. UI and UX requirements

### 11.1 Direction

Calm, fresh, eco-friendly. Mobile first for officers (phones used while standing or walking). Laptop layout for the teacher with a sidebar.

### 11.2 Color palette

| Color | Hex | Suggested role |
| --- | --- | --- |
| Teal | `#35858E` | Primary: main buttons, active menu, links |
| Sage green | `#7DA78C` | Secondary: success and delivered state, icons |
| Light olive | `#C2D099` | Soft accent: badges, row highlights |
| Cream green | `#E6EEC9` | Page background and card surfaces |

Readability notes (approximate, please verify with a contrast checker):

- White text on `#35858E` is about 4.3:1. It is fine for bold or large text. For small text, use a darker teal.
- White text on `#7DA78C` is about 2.7:1, so use dark text on that color.
- Dark text on `#C2D099` and `#E6EEC9` reads well.
- The palette has no neutral text color, gray, error red, or warning amber. The designer proposes these in harmony with the palette.

### 11.3 Pages to design

| Page | Audience | Priority |
| --- | --- | --- |
| Home | Everyone | P1 |
| Register, Login | Visitor | P1 |
| Teacher dashboard | Teacher | P1 |
| Assignments (list, add, upload, preview) | Teacher | P1 |
| My Tasks | Student | P1 |
| Today's Duty | Officers | P1 |
| Floor map | Cleaning officer | P1 (the signature screen) |
| Sorted waste | Cleaning officer | P1 |
| Activity log | Cleaning officer | P1 |
| Wednesday collection | Cleaning officer | P1 |
| Canteen sales | Canteen officer | P1 |
| Attendance | Officers | P1 |
| Toilet duty | Toilet officer | P1 |
| Archive and export | Teacher, students | P1 |
| 403 and 404 pages | Everyone | P1 |
| Student data | Teacher | P2 |

### 11.4 Navigation

| Role | Menu |
| --- | --- |
| Visitor | Home, Login, Register |
| Regular student and toilet duty student | Home, My Tasks, Archive, Logout |
| Cleaning officer | Today's Duty, Floor Map, Sorted Waste, Activity Log, Wednesday Collection, Attendance, My Tasks, Archive, Logout |
| Toilet officer | Today's Duty, Toilet Duty, Attendance, My Tasks, Archive, Logout |
| Canteen officer | Today's Duty, Canteen Sales, Attendance, My Tasks, Archive, Logout |
| Teacher | Dashboard, Assignments, Archive, Logout |

### 11.5 States and feedback

Design for every page where relevant: empty, loading or saving, success (dismissible), field validation errors under each field plus a summary, confirmation before delete, disabled state for the import Confirm button, and the 403 and 404 pages.

### 11.6 Accessibility

Tap targets of at least 44 px, base font of at least 16 px on phones, status never by color alone (use icon plus text), sufficient contrast.

### 11.7 Reference

Figma file provided by the owner: https://www.figma.com/design/DJ4p5Lq81fLITKUXkgoQqH/Untitled?node-id=0-1&t=Rmjv8yZQYUkXMcBF-1

## 12. Non-functional requirements

- Timezone: `Asia/Jakarta`. "Today" and all date rules use it.
- Locale: Indonesian for dates and messages.
- Responsive from 360 px up.
- Security: passwords hashed, CSRF on every form, authorization on the server (never only hidden menus), uploads validated by type and size, and users can only read data allowed by the permission table.
- Performance: pages with lists use pagination or limits, and queries avoid N+1 by eager loading.
- Exports stream, so large archives do not exhaust memory.

## 13. Seed and demo data

| Seed | Content |
| --- | --- |
| Classrooms | Majors PPLG, DKV, TJKT, BDP. Grade 10: X-1 to X-3. Grades 11 and 12: XI-1, XI-2, XII-1, XII-2. Adjust to the real list. |
| Semesters | 2026/2027 semester 1 (2026-07-13 to 2026-12-19) and semester 2 (2027-01-04 to 2027-06-19) |
| Floors and rooms | 3 floors, 8 rooms each, in a 4-by-2 grid (placeholder, replace with the real building) |
| Canteens | Kantin Utama, IDS, Cafe Hotel, BDP |
| Teacher | `guru@wikrama.test`, password `password123` |
| Demo students | Cleaning officer (PPLG X-1, absen 1), toilet officer (PPLG X-1, absen 2), canteen officer (PPLG X-1, absen 19), toilet duty student (TJKT X-1, absen 1), regular student (TJKT X-1, absen 2), grade 11 student (PPLG XI-1, absen 1). Password `password123`. |
| Demo assignments | Today for the first four demo students, plus a `toilet_duty` assignment on the next weekday for the regular student to test the reminder |

## 14. Acceptance criteria

1. **Status from assignment.** Given a student with a `cleaning` assignment today, when they log in, then they land on Today's Duty and see only cleaning menus. The next day, with no assignment, the same account sees the regular student menus.
2. **Floor map.** Given a cleaning officer on the floor map, when they tap a room, then it turns green with a check and the floor progress increases. Tapping again undoes it. Opening the map on another date shows no marks.
3. **Reminder.** Given a student with an assignment on the next weekday, when they open the home page today, then a banner shows the task, location, and date.
4. **Import success.** Given a valid CSV, when the teacher uploads it, then the preview lists every row as new or updated, Confirm is enabled, and after confirming the assignments appear in the list.
5. **Import errors.** Given a CSV with an unknown NIS, when the teacher uploads it, then the preview shows that row with an error message, Confirm is disabled, and nothing is saved.
6. **Two classroom limit.** Given two classrooms already on duty on a date, when a CSV adds a third classroom's duty task on that date, then those rows show the error.
7. **Collection.** Given a cleaning officer, when they type 3 digits of an NIS, select the student, choose a type, enter an amount, and save, then the entry appears in today's list and in the archive.
8. **Sorted waste.** Given an existing record for the classroom today, when the officer saves new weights, then the record is updated and no second record exists.
9. **Toilet duty.** Given a toilet officer on Lantai 1, when they open Toilet Duty, then they see only students assigned to Lantai 1 today. Adding a replacement who already has an assignment today is rejected.
10. **Archive scoping.** Given a student, when they open the archive, then they only see their own classroom's data. A teacher can filter any classroom.
11. **Export.** Given any archive filter, when the user exports, then a CSV downloads that opens in Excel with correct columns.
12. **No rankings.** No page shows any ranking, ordering by diligence, or comparison between classrooms, rooms, or canteens.

## 15. Open questions and assumptions

| ID | Item | Current assumption |
| --- | --- | --- |
| OQ-1 | Should the Wednesday collection be limited to Wednesdays? | Not enforced. The page is available on any duty day. |
| OQ-2 | Number of floors and rooms and whether each floor has several bathrooms | 3 floors, 8 rooms each. The toilet location is the floor. |
| OQ-3 | Can a toilet duty student also be an officer on the same day? | No, one assignment per student per date. |
| OQ-4 | Who records attendance of canteen and cleaning officers? | Fellow officers of the same classroom and task family (see FR-ATT-1). |
| OQ-5 | Should toilet duty include grades 11 and 12? | Yes, any student can have `toilet_duty`. |
| OQ-6 | Is the teacher spreadsheet the only way to create assignments? | Yes, plus the single-add form. No automatic generator. |
| OQ-7 | Real classroom list and real building layout | Placeholder seeds until provided. |
| OQ-8 | Student data correction page (P2) | Deferred until after the first release. |

## 16. Out of scope

The items listed in section 3.2, plus anything not described above.

## 17. Glossary

| Term | Meaning |
| --- | --- |
| Kokurikuler | Daily rotating student activity program for grade 10 |
| Rombel | A classroom or study group (example PPLG X-1) |
| Rayon | A student's residential group |
| NIS | Student identification number |
| Absen | A student's attendance (roll) number inside the classroom |
| Piket toilet | Toilet cleaning duty for a student on a given day |
| Petugas | An officer: a student with a duty assignment today |
| Limbah | Waste collected on Wednesdays: cooking oil (liters) and eggshells (kg) |
| Sampah terpilah | Sorted waste, recorded by weight in three categories |
| Arsip | Archive of records by semester and classroom |
