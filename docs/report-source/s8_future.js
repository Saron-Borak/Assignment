const H = require('./report.js');
const { P, Rich, H1, H2, Bullet, Tbl, TableCaption } = H;

module.exports = [
  H1('8. Future Scope'),
  P('The following extensions were identified but fell outside the six-week scope. They are ordered by the ratio of value to effort.'),
  Tbl(['Enhancement', 'Description', 'Effort'], [
    ['Automatic notification', 'Email or SMS a student and their advisor when attendance crosses below the threshold, rather than waiting for someone to read the report. The at-risk calculation already exists; only delivery is missing.', 'Low'],
    ['Dashboard charts', 'Attendance trend across the semester and present-versus-absent breakdowns, drawn from data the reporting service already returns.', 'Low'],
    ['PDF reports', 'Server-generated PDF for formal records. Currently covered by print stylesheets, which produce an equivalent document through the browser.', 'Low'],
    ['Attendance appeals', 'Allow a student to request that an absence be reclassified as excused, subject to lecturer approval. The excused status and its exclusion from the denominator already exist; only the workflow is missing.', 'Medium'],
    ['Timetable clash detection', 'Warn when a lecturer or a room is double-booked, or when a student enrolls in two classes that meet at the same time.', 'Medium'],
    ['Location-verified check-in', 'Require the check-in request to originate from the campus network or from within a geofence, closing the remaining gap where a student shares a live code by message.', 'Medium'],
    ['Native mobile application', 'A dedicated scanner app, removing the need to open a browser. The responsive interface already covers this adequately.', 'High'],
    ['Biometric attendance', 'Fingerprint or facial recognition at the classroom door, eliminating proxy attendance entirely but requiring hardware in every room.', 'High'],
    ['Multi-campus support', 'Extend the structure to several campuses with their own calendars and timezones.', 'High'],
  ], [2000, 5860, 1500]),
  TableCaption('Possible extensions'),

  H1('9. Conclusion'),
  P('The system meets the objectives set out in Section 1.3. The academic structure, people, class sections and rosters are all managed through the administrator portal; lecturers record attendance either by marking a register or by projecting a rotating QR code; and students see their percentage against the 75% requirement continuously, warned before they breach it. All thirty-nine functional and nine non-functional requirements were implemented and verified.'),
  P('The problem stated at the outset was that attendance data arrived too late to act on, and could not be fully trusted. Both parts are addressed. Percentages are current from the moment a register closes, which is the same lesson the class was held in. Trust is improved by removing the shared paper sheet, by rotating the check-in code so a photograph cannot be reused, and by enforcing at the database level that a student has exactly one outcome per session.'),
  P('Three aspects of the work are worth drawing out. The first is that constraints improved the design rather than degrading it: having no image extension forced the SVG route, which proved better than the PNG one would have been. The second is the value of a service layer sitting over three competing write paths, which is what allowed a late change to the closing rule to be made safely in one place. The third is that the defects which mattered most were the silent ones, found by reading output rather than by watching for errors, which is an argument for verifying a system by using it and not only by testing it.'),
  P('The system is complete, tested and documented, and can be demonstrated end to end against seeded data on any machine with PHP, Composer and MySQL.'),
];
