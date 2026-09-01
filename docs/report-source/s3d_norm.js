const H = require('./report.js');
const { P, Rich, H3, Bullet, Tbl, TableCaption } = H;

module.exports = [
  H3('3.5.3 Normalisation'),
  P('The schema is in third normal form.'),
  Bullet('First normal form: every column holds a single atomic value. The weekly timetable, which would naturally invite a comma-separated list of days, is instead a separate class_schedules table with one row per slot.'),
  Bullet('Second normal form: no non-key column depends on only part of a composite key. The junction tables use a surrogate primary key with a unique constraint over the natural key, so partial dependency cannot arise.'),
  Bullet('Third normal form: no non-key column depends on another non-key column. A course title is stored once on courses and referenced by every section rather than repeated on each; a student name lives on users and is never copied onto attendance records.'),
  P('One denormalisation was considered and rejected: caching each student\u2019s attendance percentage on the enrollments row. It would have made reporting trivial, but the value would need recalculating on every attendance write and would silently drift if any path missed the update. The percentage is therefore always derived, and Section 4.7 explains how it is kept inexpensive.'),

  H3('3.5.4 Integrity Constraints'),
  P('Correctness that matters is enforced by the database, not only by application code, so a bug or a direct SQL edit cannot corrupt the record.'),
  Tbl(['Constraint', 'Table', 'Purpose'], [
    ['UNIQUE (attendance_session_id, student_id)', 'attendance_records', 'A student has exactly one outcome per session. This is what makes check-in idempotent: a double scan cannot create a second record.'],
    ['UNIQUE (class_section_id, student_id)', 'enrollments', 'A student cannot appear twice on the same roster.'],
    ['UNIQUE (course_id, semester_id, section_code)', 'class_sections', 'A course cannot run two sections with the same letter in one semester.'],
    ['UNIQUE (class_section_id, session_date, start_time)', 'attendance_sessions', 'A section cannot meet twice at the same moment, which makes session generation safe to re-run.'],
    ['UNIQUE (qr_token)', 'attendance_sessions', 'A check-in token resolves to exactly one session.'],
    ['UNIQUE (email)', 'users', 'One account per email address.'],
    ['FOREIGN KEY, ON DELETE CASCADE', 'enrollments, attendance_records, class_schedules', 'Removing a parent removes its dependent rows, so no orphans remain.'],
    ['FOREIGN KEY, ON DELETE RESTRICT', 'courses, lecturers, students', 'Prevents deleting a faculty, program or lecturer that is still referenced.'],
  ], [3100, 2200, 4060]),
  TableCaption('Integrity constraints and their purpose'),
  Rich([
    'A note on index naming: MySQL limits an identifier to 64 characters, and Laravel\u2019s automatically generated name for the session uniqueness constraint exceeded it. These constraints are therefore given explicit short names in the migrations, for example ',
    { text: 'sessions_section_date_time_unique', font: 'Consolas', size: 18 },
    '.',
  ]),
];
