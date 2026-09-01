const H = require('./report.js');
const { P, Rich, H1, H2, H3, Bullet, Num, Code, Tbl, TableCaption, Figure, GREY } = H;

module.exports = [
  H1('6. System Walkthrough'),
  P('This section shows the finished system screen by screen, following the same path used for the demonstration. Every screenshot is taken from the seeded database, so the figures shown match those quoted elsewhere in this report.'),
  Rich([
    { text: 'To capture these screenshots: ', bold: true },
    'start the server with ',
    { text: 'php artisan serve', font: 'Consolas', size: 19 },
    ', open ',
    { text: 'http://localhost:8000', font: 'Consolas', size: 19 },
    ', and sign in with the accounts listed in Appendix B. Each placeholder below names the exact screen and address to capture.',
  ]),

  H2('6.1 Signing In'),
  Figure('The sign-in screen', 'Sign out, then capture http://localhost:8000/login showing the university crest, title and the email and password fields.'),
  P('Accounts are issued by the registry; there is no public registration, which matches how a university actually admits students. Entering an incorrect password produces the message shown below rather than revealing whether the address exists.'),
  Figure('Rejected sign-in attempt', 'On the login page, submit admin@eamu.edu with a deliberately wrong password and capture the red error banner.'),

  H2('6.2 Administrator Portal'),
  H3('6.2.1 Dashboard'),
  P('The dashboard opens on four headline figures and two panels: the classes scheduled for today across the whole university, and the students currently below the attendance threshold.'),
  Figure('Administrator dashboard', 'Sign in as admin@eamu.edu and capture /admin/dashboard, showing the four statistic cards, today classes and the at-risk panel.'),

  H3('6.2.2 Managing the Academic Structure'),
  P('Faculties, programs, courses and semesters follow a consistent pattern: a searchable list with counts of dependent records, and a form for creating or editing.'),
  Figure('Course catalogue', 'Capture /admin/courses, showing the search and faculty filter, the course table and the section counts.'),
  Figure('Course entry form', 'Capture /admin/courses/create, showing the validated fields for code, title, credit hours and faculty.'),
  P('Attempting to delete a record that still has dependents is refused with an explanation rather than cascading silently.'),
  Figure('Referential integrity guard', 'On /admin/faculties, attempt to delete a faculty that still owns courses, and capture the resulting error banner.'),

  H3('6.2.3 Managing People'),
  P('Creating a lecturer or a student creates both the academic record and the sign-in account in a single transaction, so the two can never fall out of step.'),
  Figure('Student list', 'Capture /admin/students, showing the search, the program filter, student numbers and the class counts.'),
  Figure('Student entry form', 'Capture /admin/students/create, showing the Account and Student record sections of the form.'),

  H3('6.2.4 Class Sections and Timetables'),
  P('A class section binds a course, a semester and a lecturer, and carries the weekly timetable from which a lecturer later generates a term of sessions.'),
  Figure('Class section list', 'Capture /admin/class-sections, showing the timetable column and the roster occupancy figures.'),
  Figure('Class section form with timetable builder', 'Capture /admin/class-sections/create, scrolled to show the Weekly timetable builder with its add and remove controls.'),

  H3('6.2.5 Roster Management'),
  P('The roster manager places the enrolled list beside a searchable pool of candidates. Students already enrolled are excluded from the pool, so a duplicate cannot be created by mistake.'),
  Figure('Roster manager', 'From /admin/class-sections, open Manage roster on any section and capture the two-column screen with several candidates selected.'),

  H3('6.2.6 Account Administration'),
  P('The system sends no email, so password resets are performed by the registry in person. An administrator cannot deactivate their own account.'),
  Figure('User accounts', 'Capture /admin/users, showing the role filter, status badges and the Reset password and Deactivate controls.'),

  H3('6.2.7 Reporting'),
  P('The overview reports every class section with its cohort attendance. Rows below the threshold are highlighted.'),
  Figure('University attendance overview', 'Capture /admin/reports, showing the four summary cards and the per-section table.'),
  P('The at-risk report is the operationally important one: it lists every enrollment below the required minimum, worst first, so the registry can intervene while the semester is still running.'),
  Figure('At-risk students report', 'Capture /admin/reports/low-attendance, showing the ordered list with red highlighting and the Download CSV button.'),
  Figure('Exported CSV opened in a spreadsheet', 'Click Download CSV on the at-risk report, open the downloaded file in Excel and capture the sheet showing the column headings and several rows.'),

  H2('6.3 Lecturer Portal'),
  H3('6.3.1 Dashboard'),
  P('A lecturer sees only the classes assigned to them. The dashboard leads with the sessions scheduled for today, each with a single control to open it for check-in.'),
  Figure('Lecturer dashboard', 'Sign in as c.nou@eamu.edu and capture /lecturer/dashboard, showing the statistic cards, today sessions with the Open button, and the My classes panel.'),

  H3('6.3.2 Class Detail'),
  Figure('Class detail', 'From My classes, open any class and capture the screen showing the session list on the left and the roster attendance panel on the right.'),

  H3('6.3.3 Generating Sessions'),
  P('Rather than creating each class meeting by hand, a lecturer generates the whole term from the timetable in one action. Existing sessions are skipped, so the operation is safe to repeat.'),
  Figure('Session generation', 'Capture the Add / generate screen for a class, showing the timetable slots and the date-range form.'),

  H3('6.3.4 Marking the Register'),
  P('The register is the screen a lecturer uses most. Each student occupies one row with four options, present pre-selected. Students who have already checked in themselves are annotated with how they did so.'),
  Figure('Marking register', 'Open a session and capture the Mark register screen, showing the roster, the four-option button groups, the mark-all shortcuts and the late-threshold note.'),
  P('Saving and closing in one action records the register and marks any remaining student absent.'),
  Figure('Session closed', 'Close a session and capture the summary screen showing the confirmation message and the present, late, absent and excused counts.'),

  H3('6.3.5 The QR Check-in Screen'),
  P('This screen is designed to be projected. It shows the rotating QR code, the six-character alternative, a countdown to the next rotation, and a live list of students as they check in.'),
  Figure('QR check-in screen', 'Open a session and capture /lecturer/sessions/{id}/qr in full, showing the QR code, the six-character code, the countdown and the check-in counters.'),
  Rich([
    { text: 'Note for the capture: ', bold: true },
    'if the application address is still set to localhost, an amber warning appears explaining that phones cannot reach that address. Capturing it is worthwhile, since it demonstrates the system detecting its own misconfiguration.',
  ]),
  Figure('Live check-in feed', 'With the projection screen open, check in as a student from another browser, wait for the next poll, and capture the screen showing the updated counters and the student name in the feed.'),

  H2('6.4 Student Portal'),
  H3('6.4.1 Dashboard'),
  P('A student sees their overall percentage, a per-class breakdown, and a warning naming any class in which they have fallen below the requirement.'),
  Figure('Student dashboard', 'Sign in as a student and capture /student/dashboard, showing the statistic cards and the per-class attendance table with its percentage bars.'),
  Figure('Below-threshold warning', 'Sign in as one of the at-risk students listed on the administrator report, and capture the red warning banner naming the affected classes.'),

  H3('6.4.2 Checking In'),
  P('When a lecturer has a session open, the student dashboard detects it and offers a shortcut. The check-in screen accepts the six-character code for students without a working camera.'),
  Figure('Open session detected', 'With a session open, capture the green banner at the top of the student dashboard offering Check in now.'),
  Figure('Check-in screen', 'Capture /student/check-in, showing the code field, the explanatory notes and the recent self check-ins panel.'),
  Figure('Successful check-in', 'Submit a valid code and capture the green confirmation at the top of the dashboard.'),
  Figure('Rejected check-in', 'Submit an expired or invalid code and capture the red error message.'),

  H3('6.4.3 Attendance History'),
  Figure('Attendance by class', 'Capture /student/attendance, showing every enrolled class with its counts and percentage bar.'),
  Figure('Session-by-session history', 'Open one class and capture the detail screen showing each completed session with its status, the time recorded and how it was captured.'),

  H2('6.5 Access Control in Practice'),
  P('The two-layer authorisation described in Section 4.3 is visible to the user as a clean refusal rather than an error.'),
  Figure('Access denied', 'While signed in as a student or lecturer, request /admin/students and capture the access-denied page with its route back to the correct dashboard.'),

  H2('6.6 Responsive Layout'),
  P('Students check in on a phone, so the interface must work at that size. The sidebar collapses behind a menu control and tables scroll horizontally rather than overflowing the page.'),
  Figure('Mobile layout', 'Narrow the browser window to roughly 400 pixels, or use the browser device toolbar, and capture the student dashboard at phone width.'),
];
