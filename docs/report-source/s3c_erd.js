const H = require('./report.js');
const { P, Rich, H2, H3, Bullet, Code, Tbl, TableCaption, GREY } = H;

const before = [
  H2('3.5 Database Design'),
  P('The database holds twelve domain tables plus Laravel\u2019s own supporting tables for sessions, cache and queued jobs. The design separates authentication from role-specific data: every person has exactly one row in users, and lecturers and students each have an additional profile row holding the attributes only that role needs. Administrators have no profile row, because they have no academic attributes.'),

  H3('3.5.1 Entity Relationship Diagram'),
  Code([
    '  faculties ----< programs ----< students >---- users',
    '      |                              |            |',
    '      |                              |            |',
    '      +----< courses                 |       lecturers',
    '      |         |                    |            |',
    '      +----< lecturers >-------------|------------+',
    '                |                    |',
    '                v                    v',
    '  semesters >--- class_sections ---< enrollments',
    '                 |          |',
    '                 |          +----< class_schedules',
    '                 v',
    '      attendance_sessions ----< attendance_records >---- students',
    '',
    '  Legend:  ----<  one-to-many        >----  many-to-one',
  ]),
  Rich([{ text: 'Figure 2: Entity relationship diagram.', italics: true, size: 18, color: GREY }], { after: 200 }),

  P('The relationships in full:'),
  Tbl(['Parent', 'Child', 'Type', 'Meaning'], [
    ['users', 'lecturers', '1 : 0..1', 'A lecturer account has one staff profile.'],
    ['users', 'students', '1 : 0..1', 'A student account has one student profile.'],
    ['faculties', 'programs', '1 : N', 'A faculty offers many programs.'],
    ['faculties', 'courses', '1 : N', 'A faculty owns many courses.'],
    ['faculties', 'lecturers', '1 : N', 'A faculty employs many lecturers.'],
    ['programs', 'students', '1 : N', 'A program admits many students.'],
    ['courses', 'class_sections', '1 : N', 'A course runs as several sections.'],
    ['semesters', 'class_sections', '1 : N', 'A semester contains many sections.'],
    ['lecturers', 'class_sections', '1 : N', 'A lecturer teaches several sections.'],
    ['class_sections', 'class_schedules', '1 : N', 'A section meets in one or more weekly slots.'],
    ['class_sections', 'enrollments', '1 : N', 'A section has a roster of students.'],
    ['class_sections', 'attendance_sessions', '1 : N', 'A section holds many class meetings.'],
    ['students', 'enrollments', '1 : N', 'A student is enrolled in several sections.'],
    ['attendance_sessions', 'attendance_records', '1 : N', 'A session produces one record per student.'],
    ['students', 'attendance_records', '1 : N', 'A student accumulates many records.'],
  ], [2000, 2400, 1200, 3760]),
  TableCaption('Entity relationships'),

  P('The many-to-many relationship between students and class sections is resolved through the enrollments table, and the many-to-many between students and sessions through attendance_records. Both junction tables carry their own attributes, so neither is a simple link table.'),

  H3('3.5.2 Table Structures'),
  P('The tables below were generated from the live database. Types are as MariaDB reports them; the validation column records the rules the application enforces before a write is attempted.'),
];

module.exports = { before };
