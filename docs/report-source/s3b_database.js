const H = require('./report.js');
const fs = require('fs');
const path = require('path');
const { P, Rich, H2, H3, Bullet, Code, Tbl, TableCaption, GREY } = H;

// Validation notes, keyed "table.column", merged into the generated table designs.
const NOTES = {
  'users.email': 'Required, valid email, unique',
  'users.password': 'Required, min 8 chars, bcrypt hashed',
  'users.role': 'One of: admin, lecturer, student',
  'users.is_active': 'Blocks sign-in when false',
  'faculties.code': 'Required, uppercase, unique, max 20',
  'programs.code': 'Required, uppercase, unique, max 20',
  'semesters.end_date': 'Must be after start_date',
  'semesters.is_active': 'Only one semester may be true',
  'lecturers.staff_no': 'Required, unique',
  'students.student_no': 'Required, unique',
  'students.intake_year': 'Integer, 2000 to current year + 1',
  'students.status': 'One of: active, inactive, graduated',
  'courses.code': 'Required, uppercase, unique, max 20',
  'courses.credit_hours': 'Integer, 1 to 12',
  'class_sections.section_code': 'Alphanumeric; unique per course and semester',
  'class_sections.capacity': 'Integer, 1 to 500',
  'class_schedules.day_of_week': 'Integer 1 to 7 (ISO: 1 = Monday)',
  'class_schedules.end_time': 'Must be after start_time',
  'enrollments.status': 'One of: enrolled, dropped',
  'attendance_sessions.status': 'One of: scheduled, open, closed',
  'attendance_sessions.qr_token': 'Random 64 chars; null unless open',
  'attendance_sessions.checkin_code': 'Six chars from a no-ambiguity alphabet',
  'attendance_sessions.late_after_minutes': 'Grace period before a check-in is late',
  'attendance_records.status': 'One of: present, late, absent, excused',
  'attendance_records.marked_via': 'One of: manual, qr, code, system',
  'attendance_records.remarks': 'Optional, max 255',
};

const KEY = { PRI: 'PK', UNI: 'Unique', MUL: 'FK / Index' };

const ORDER = [
  ['users', 'Authentication and role for every person in the system.'],
  ['faculties', 'Top-level academic divisions.'],
  ['programs', 'Degree programs students are admitted into.'],
  ['semesters', 'Academic periods; exactly one is active.'],
  ['lecturers', 'Teaching-staff profile attached to a user account.'],
  ['students', 'Student profile attached to a user account.'],
  ['courses', 'The course catalogue.'],
  ['class_sections', 'One offering of a course, in one semester, taught by one lecturer.'],
  ['class_schedules', 'Weekly timetable slots for a class section.'],
  ['enrollments', 'Which students are on which roster.'],
  ['attendance_sessions', 'A single class meeting.'],
  ['attendance_records', 'One student outcome for one session.'],
];

const raw = fs.readFileSync(path.join(__dirname, 'schema.txt'), 'utf8').trim().split(/\r?\n/);
const byTable = {};
for (const line of raw) {
  const [table, column, type, nullable, key, def] = line.split('|');
  (byTable[table] ||= []).push({ column, type, nullable, key, def });
}

const shortType = (t) => t
  .replace('bigint(20) unsigned', 'bigint UN')
  .replace('tinyint(3) unsigned', 'tinyint UN')
  .replace('smallint(5) unsigned', 'smallint UN')
  .replace(/varchar\((\d+)\)/, 'varchar($1)');

const tableDesigns = [];
for (const [table, purpose] of ORDER) {
  const cols = byTable[table] || [];
  tableDesigns.push(H3(`3.5.2.${tableDesigns.length / 3 + 1} ${table}`));
  tableDesigns.push(P(purpose, { after: 100 }));
  tableDesigns.push(Tbl(
    ['Column', 'Type', 'Null', 'Key', 'Validation / notes'],
    cols.map(c => [
      c.column,
      shortType(c.type),
      c.nullable === 'YES' ? 'Yes' : 'No',
      KEY[c.key] || '',
      NOTES[`${table}.${c.column}`] || (c.column === 'id' ? 'Auto-increment' : (c.column.endsWith('_at') && !NOTES[`${table}.${c.column}`] ? 'Timestamp' : '')),
    ]),
    [2100, 1700, 700, 1200, 3660],
  ));
}

module.exports = { tableDesigns, byTable };
