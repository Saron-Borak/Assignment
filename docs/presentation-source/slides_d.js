const D = require('./deck.js');
const {
  pres, newSlide, heading, statusDots, card, stat,
  NAVY, NAVY_2, GOLD, INK, MUTED, LINE, TINT, WHITE,
  GREEN, AMBER, RED, SLATE, HEAD, BODY, W, Hh, M, CW,
} = D;

// Small labelled entity box for the schema diagram.
function entity(s, x, y, w, h, label, fill, color) {
  s.addShape(pres.ShapeType.roundRect, {
    x, y, w, h, rectRadius: 0.05,
    fill: { color: fill }, line: { color: color || LINE, width: 1 },
  });
  s.addText(label, {
    x: x + 0.06, y: y + 0.055, w: w - 0.12, h: h - 0.11, isTextBox: true, margin: 0,
    align: 'center', valign: 'middle', fontFace: BODY, fontSize: 10.5, bold: true,
    color: fill === NAVY ? WHITE : NAVY,
  });
}

function link(s, x1, y1, x2, y2) {
  s.addShape(pres.ShapeType.line, {
    x: x1, y: y1, w: x2 - x1, h: y2 - y1,
    line: { color: 'AEB8C8', width: 1.25 },
  });
}

// ===========================================================================
// 6 - DATABASE DESIGN
// ===========================================================================
{
  const s = newSlide(false);
  heading(s, 'Twelve tables, third normal form', 'Authentication is separated from role data: one users table, with a profile row for each role that needs one.');

  const bw = 1.62, bh = 0.44;
  const cx = M + 0.1;

  // Row 1 - reference data
  entity(s, cx,             1.94, bw, bh, 'faculties',   TINT);
  entity(s, cx + 2.0,       1.94, bw, bh, 'programs',    TINT);
  entity(s, cx + 4.0,       1.94, bw, bh, 'courses',     TINT);
  entity(s, cx + 6.0,       1.94, bw, bh, 'semesters',   TINT);
  entity(s, cx + 8.0,       1.94, bw, bh, 'users',       NAVY, NAVY);

  // Row 2 - profiles
  entity(s, cx + 2.0,       2.78, bw, bh, 'students',    TINT);
  entity(s, cx + 8.0,       2.78, bw, bh, 'lecturers',   TINT);

  // Row 3 - the join
  entity(s, cx + 4.0,       3.62, bw, bh, 'class_sections', 'E4EAF4');
  entity(s, cx + 6.0,       3.62, bw, bh, 'class_schedules', TINT);
  entity(s, cx + 2.0,       3.62, bw, bh, 'enrollments', TINT);

  // Row 4 - the attendance core
  entity(s, cx + 4.0,       4.46, bw, bh, 'attendance_sessions', 'E4EAF4');
  entity(s, cx + 2.0,       4.46, bw, bh, 'attendance_records',  'E4EAF4');

  link(s, cx + 0.81, 2.38, cx + 2.81, 2.78);        // faculties -> programs area
  link(s, cx + 2.81, 2.38, cx + 2.81, 2.78);        // programs -> students
  link(s, cx + 8.81, 2.38, cx + 8.81, 2.78);        // users -> lecturers
  link(s, cx + 8.81, 2.38, cx + 2.81, 2.78);        // users -> students
  link(s, cx + 4.81, 2.38, cx + 4.81, 3.62);        // courses -> class_sections
  link(s, cx + 6.81, 2.38, cx + 5.62, 3.62);        // semesters -> class_sections
  link(s, cx + 8.81, 3.22, cx + 5.62, 3.62);        // lecturers -> class_sections
  link(s, cx + 5.62, 3.84, cx + 6.0,  3.84);        // sections -> schedules
  link(s, cx + 4.0,  3.84, cx + 3.62, 3.84);        // sections -> enrollments
  link(s, cx + 4.81, 4.06, cx + 4.81, 4.46);        // sections -> sessions
  link(s, cx + 4.0,  4.68, cx + 3.62, 4.68);        // sessions -> records
  link(s, cx + 2.81, 4.06, cx + 2.81, 4.46);        // enrollments -> records

  card(s, {
    x: M, y: 5.14, w: (CW - 0.36) / 2, h: 1.6,
    kicker: 'THE CONSTRAINT THAT MATTERS MOST',
    title: 'UNIQUE (session, student)',
    body: 'One outcome per student per session, enforced by the database. This is what makes a double scan harmless rather than a duplicate.',
    titleSize: 14.5, bodySize: 11.5, titleColor: NAVY,
  });
  card(s, {
    x: M + (CW - 0.36) / 2 + 0.36, y: 5.14, w: (CW - 0.36) / 2, h: 1.6,
    kicker: 'A DELIBERATE OMISSION',
    kickerColor: AMBER,
    title: 'No cached percentage',
    body: 'It would make reporting trivial, but it would drift the moment any write path forgot to update it. So it is always derived.',
    titleSize: 14.5, bodySize: 11.5, titleColor: NAVY,
  });

  s.addNotes([
    "[3:20-4:05] Database design. 45 seconds.",
    "",
    "Twelve tables in third normal form. The shape to notice: users, in navy, is the single authentication table. Students and lecturers are profile tables hanging off it. Administrators have no profile row, because they have no academic attributes.",
    "",
    "Bottom left is the most important constraint in the schema: unique on session plus student. One outcome per student per session, enforced by the database rather than by application code. That's what makes check-in safe - scan twice and the second insert simply cannot happen.",
    "",
    "Bottom right is a decision I'll defend: I don't cache the percentage anywhere. It would make reporting trivial, but it would drift the moment any write path forgot to update it. So it's always derived.",
  ].join('\n'));
}

// ===========================================================================
// 7 - ARCHITECTURE
// ===========================================================================
{
  const s = newSlide(false);
  heading(s, 'One service, three ways in', 'Attendance can be written by three different paths. Only one of them contains the rules.');

  const bx = M, bw = 6.6;
  const steps = [
    ['Lecturer marks the register', GREEN],
    ['Student scans the QR code', GOLD],
    ['Student types the 6-digit code', AMBER],
  ];
  steps.forEach((t, i) => {
    const y = 2.0 + i * 0.62;
    s.addShape(pres.ShapeType.roundRect, {
      x: bx, y, w: 3.5, h: 0.5, rectRadius: 0.04,
      fill: { color: WHITE }, line: { color: t[1], width: 1.5 },
    });
    s.addText(t[0], {
      x: bx + 0.18, y: y + 0.11, w: 3.14, h: 0.3, isTextBox: true, margin: 0,
      fontFace: BODY, fontSize: 11.5, bold: true, color: NAVY,
    });
    s.addShape(pres.ShapeType.line, {
      x: bx + 3.5, y: y + 0.25, w: 0.66, h: 2.86 - (y + 0.25) + 0.24,
      line: { color: 'AEB8C8', width: 1.25, endArrowType: 'triangle' },
    });
  });

  s.addShape(pres.ShapeType.roundRect, {
    x: bx + 4.16, y: 2.62, w: 2.44, h: 0.98, rectRadius: 0.05,
    fill: { color: NAVY }, line: { color: NAVY, width: 0 },
  });
  s.addText('AttendanceService', {
    x: bx + 4.24, y: 2.78, w: 2.28, h: 0.3, isTextBox: true, margin: 0,
    align: 'center', fontFace: HEAD, fontSize: 14, bold: true, color: WHITE,
  });
  s.addText('every write', {
    x: bx + 4.24, y: 3.1, w: 2.28, h: 0.26, isTextBox: true, margin: 0,
    align: 'center', fontFace: BODY, fontSize: 10.5, color: GOLD,
  });

  s.addShape(pres.ShapeType.line, {
    x: bx + 5.38, y: 3.6, w: 0, h: 0.5,
    line: { color: 'AEB8C8', width: 1.25, endArrowType: 'triangle' },
  });
  s.addShape(pres.ShapeType.roundRect, {
    x: bx + 4.16, y: 4.1, w: 2.44, h: 0.62, rectRadius: 0.04,
    fill: { color: 'E4EAF4' }, line: { color: LINE, width: 1 },
  });
  s.addText('attendance_records', {
    x: bx + 4.24, y: 4.26, w: 2.28, h: 0.3, isTextBox: true, margin: 0,
    align: 'center', fontFace: BODY, fontSize: 11.5, bold: true, color: NAVY,
  });

  s.addText('Controllers stay thin. The rules live in one class, so the three paths cannot disagree.', {
    x: bx, y: 5.06, w: bw, h: 0.4, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 12, italic: true, color: MUTED,
  });

  const rx = M + bw + 0.5, rw = W - M - rx;
  card(s, {
    x: rx, y: 1.94, w: rw, h: 2.16,
    kicker: 'WHY IT MATTERS',
    title: 'Three copies would drift',
    body: 'If each path had its own rules, they would eventually disagree about what counts as late, or about who may be marked - and the disagreement would surface as data nobody can reconcile.',
    titleSize: 15, bodySize: 12,
  });
  card(s, {
    x: rx, y: 4.26, w: rw, h: 2.2,
    kicker: 'IT PAID OFF',
    kickerColor: GREEN,
    title: 'A late change, made once',
    body: 'Mid-project the closing rule changed, so that closing a register also retires the check-in code. That was one edit in one method, and all three paths inherited it automatically.',
    titleSize: 15, bodySize: 12,
  });

  s.addNotes([
    "[4:05-4:50] Architecture. 45 seconds.",
    "",
    "This is the design decision I'm most confident about.",
    "",
    "Attendance can be written three ways: a lecturer marking a register, a student scanning a code, or a student typing one. Three entry points, one destination. All three go through a single service. No controller contains an attendance rule.",
    "",
    "The reason is on the right. Three copies of the logic would eventually disagree - about what counts as late, or who may be marked - and you'd get contradictory records with no way to tell which is right.",
    "",
    "And it wasn't theoretical. Mid-project the closing rule changed: closing a register should also retire the check-in code. One service meant one edit, and all three paths inherited it. With three copies I'd have missed one.",
  ].join('\n'));
}
