const H = require('./report.js');
const { P, Rich, H1, H2, H3, Bullet, Num, Code, Tbl, TableCaption } = H;

module.exports = [
  H1('7. Challenges Faced'),
  P('Six problems required a design decision rather than a routine fix. Each is recorded with its reasoning, since the reasoning is more transferable than the fix.'),

  H2('7.1 No Image Extension for QR Generation'),
  P('The obvious way to produce a QR code in PHP is to render a PNG, but that requires the gd or imagick extension and the development machine has neither. Installing an extension was not an acceptable answer: the system has to run on a standard XAMPP installation, and a marker cannot be asked to recompile PHP.'),
  P('The available libraries were therefore evaluated against this constraint before anything else. The one selected emits SVG using nothing but core PHP, listing the image extension only as an optional suggestion for PNG output. Choosing on the constraint rather than on popularity turned a limitation into an improvement: vector output scales to any projector without blurring, and embeds directly in the page, avoiding a second request per rotation.'),

  H2('7.2 Preventing Proxy Attendance'),
  P('A static QR code defeats itself. The first student to arrive photographs it and sends it to a friend who is elsewhere, and the system has recorded a lie.'),
  P('The solution was to give the code a short life and rotate it continuously. Tokens expire after sixty seconds and the projected page replaces them every forty-five, so a photograph is worthless almost at once. This introduced a secondary problem: a scan arriving in the moment between rotations would be rejected. The alternatives were to accept the previous token for a grace period, which needs extra columns and weakens the guarantee, or to keep a single valid token and provide a fallback. The second was chosen, because a rejected scan is recoverable in two seconds by scanning again, whereas a weakened guarantee is permanent. The six-character code covers that edge case and also serves students whose camera does not work.'),

  H2('7.3 An Index Name Too Long for MySQL'),
  P('The first migration run failed outright. MySQL limits an identifier to 64 characters, and the name the framework generated for the session uniqueness constraint, built by concatenating the table name with all three column names, exceeded it.'),
  P('Five constraints were given explicit short names. The wider lesson was to run migrations against the real database early, rather than trusting that a schema definition which looks correct will apply cleanly.'),

  H2('7.4 A Silent Query-Builder Interaction'),
  P('The most instructive defect in the project produced no error at all. Several list screens showed empty cells where a count should have appeared, because a select call placed after a withCount call replaced the counting subquery rather than adding to it.'),
  P('What makes this worth recording is the failure mode. The page returned HTTP 200, the layout was intact, and only a number was missing, so a test asserting that the page loads could not detect it. It was found by reading the rendered output during manual verification. The remedy removed the need for the select entirely, by ordering through a subquery instead of a join, and the pattern is now written down as a project convention so it cannot be reintroduced.'),

  H2('7.5 Timezone Against Timetable'),
  P('A check-in made at 10:28 for a class that started at 08:00 was recorded as present rather than late. The application was running in UTC while the timetable stores local wall-clock times, so the server believed the class had not yet begun.'),
  P('This illustrates a defect the automated tests were structurally unable to find. Every test builds its times relative to the present moment, so they stay internally consistent in any timezone and pass regardless. Only a fixed timetable time compared against a real clock exposes the mismatch. The application timezone is now set explicitly, with the reason recorded in the configuration file so it is not reverted by someone assuming UTC is a safe default.'),

  H2('7.6 Keeping Three Write Paths Consistent'),
  P('Attendance can be recorded by a lecturer marking a register, by a student scanning a code, or by a student typing one. Three separate implementations of the same rules would eventually disagree about what counts as late or about who may be marked, and that disagreement would surface as data nobody can reconcile.'),
  P('All three paths therefore call the same service. When the closing rule changed during development, so that closing retires the check-in credentials, the change was made once and applied everywhere. The same reasoning governs the read side: the percentage rule exists in exactly one method, so a screen and its CSV export cannot report different figures for the same student.'),
];
