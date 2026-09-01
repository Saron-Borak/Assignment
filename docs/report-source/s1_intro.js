const H = require('./report.js');
const { P, Rich, H1, H2, H3, Bullet, Num, Figure, Code, Tbl, TableCaption, GREY } = H;

module.exports = [
  H1('1. Introduction'),

  H2('1.1 Background'),
  P('East Asia Management University (EAMU) records class attendance on paper registers. A lecturer carries a printed roster to each class, ticks names by hand, and the sheets are later collected by the faculty office. The sheets are then either filed unprocessed or copied into a spreadsheet at the end of the semester.'),
  P('The university requires every student to attend at least 75% of the sessions held for each class they are enrolled in. A student who falls below that line may be barred from the final examination. Under a paper system, however, nobody discovers that a student has crossed the threshold until the register sheets are totalled — typically far too late for the student to recover.'),

  H2('1.2 Problem Statement'),
  P('The existing paper-based process suffers from four specific, related problems:'),
  Bullet('Attendance percentages are not visible while they still matter. Totals are computed at the end of the semester, so neither the student nor the lecturer can act on a downward trend during it.'),
  Bullet('Marking a register by hand consumes teaching time. In a class of thirty students, calling a roll costs several minutes of every session.'),
  Bullet('Paper registers are easy to lose and easy to falsify. A sheet passed around the room can be signed by a classmate on behalf of an absent student, and a mislaid sheet destroys the record for that session entirely.'),
  Bullet('There is no single source of truth. The faculty office, the lecturer and the student can each hold a different view of the same attendance record, with no way to reconcile them.'),
  P('The consequence is that a rule the university actually enforces — the 75% requirement — is administered using data that arrives too late to be useful and cannot be fully trusted.'),

  H2('1.3 Aim and Objectives'),
  P('The aim of this project is to build a web-based attendance management system that records attendance at the moment a class meets, and makes the resulting percentage continuously visible to everyone who needs it.'),
  P('The specific objectives are:'),
  Num('To design a normalised relational database capable of representing the university\u2019s academic structure, its class rosters, and every attendance event.'),
  Num('To implement three role-specific portals — administrator, lecturer and student — each restricted to the data that role is entitled to see.'),
  Num('To provide a fast marking interface so a lecturer can complete a register in seconds rather than minutes.'),
  Num('To provide a self check-in mechanism, using a rotating QR code, that removes roll-calling from the classroom entirely while resisting proxy attendance.'),
  Num('To calculate attendance percentages automatically against the university\u2019s 75% rule, and to surface at-risk students while intervention is still possible.'),
  Num('To validate the system through automated tests covering authorisation, data integrity and the correctness of the percentage calculation.'),

  H2('1.4 Scope'),
  H3('1.4.1 In Scope'),
  Bullet('Management of the academic structure: faculties, programs, courses, semesters and class sections.'),
  Bullet('Management of people: lecturer and student records, each paired with a sign-in account.'),
  Bullet('Class rosters, with bulk enrollment.'),
  Bullet('Weekly timetables per class section, and bulk generation of a semester of class sessions from them.'),
  Bullet('Attendance marking by the lecturer, with four outcomes: present, late, absent and excused.'),
  Bullet('Student self check-in by scanning a rotating QR code or typing a short code.'),
  Bullet('Attendance reporting at university, class and student level, with CSV download and print support.'),
  Bullet('Role-based access control with per-record ownership checks.'),

  H3('1.4.2 Out of Scope'),
  P('The following were deliberately excluded to keep the project deliverable within the six-week timeframe. They are revisited in Section 8, Future Scope.'),
  Bullet('Grade and assessment management — the system records attendance only.'),
  Bullet('Timetable clash detection and automated room allocation.'),
  Bullet('Outbound email or SMS notification. The system has no mail server dependency; password resets are performed by an administrator in person.'),
  Bullet('A native mobile application. The interface is responsive and works in a phone browser, which is sufficient for QR check-in.'),
  Bullet('Biometric or location-verified attendance.'),

  H2('1.5 Structure of this Report'),
  P('Section 2 presents the software requirements specification. Section 3 covers user interface and database design. Section 4 describes the implementation, including the security measures applied. Section 5 reports the testing performed and the defects it uncovered. Section 6 is a screen-by-screen walkthrough of the finished system. Sections 7 and 8 discuss the challenges encountered and the work that could follow, and Section 9 concludes.'),
];
