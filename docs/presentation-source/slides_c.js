const D = require('./deck.js');
const {
  pres, newSlide, heading, statusDots, card, stat,
  NAVY, NAVY_2, GOLD, INK, MUTED, LINE, TINT, WHITE,
  GREEN, AMBER, RED, SLATE, HEAD, BODY, W, Hh, M, CW,
} = D;

// ===========================================================================
// 4 - THREE PORTALS
// ===========================================================================
{
  const s = newSlide(false);
  heading(s, 'Three roles, three portals', 'One application, but each role only ever sees the data it is entitled to.');

  const roles = [
    ['ADMINISTRATOR', 'The registry', [
      'Faculties, programs, courses, semesters',
      'Lecturer and student accounts',
      'Class sections and weekly timetables',
      'Rosters and bulk enrollment',
      'All reports, university-wide',
    ], NAVY],
    ['LECTURER', 'Their own classes only', [
      'Generate a term of sessions from the timetable',
      'Mark the register in seconds',
      'Project the rotating QR code',
      'Close the session, auto-marking absences',
      'Reports for their own cohort',
    ], GREEN],
    ['STUDENT', 'Their own record only', [
      'Attendance percentage for every class',
      'Warning when below the 75% rule',
      'Session-by-session history',
      'Self check-in by QR or typed code',
    ], GOLD],
  ];

  const cw = (CW - 0.72) / 3;
  roles.forEach((r, i) => {
    const x = M + i * (cw + 0.36);
    const y = 1.86, h = 4.32;
    s.addShape(pres.ShapeType.roundRect, {
      x, y, w: cw, h, rectRadius: 0.05,
      fill: { color: TINT }, line: { color: LINE, width: 1 },
    });
    s.addShape(pres.ShapeType.ellipse, {
      x: x + 0.3, y: y + 0.3, w: 0.4, h: 0.4, fill: { color: r[3] },
    });
    s.addText(r[0], {
      x: x + 0.86, y: y + 0.35, w: cw - 1.12, h: 0.3, isTextBox: true, margin: 0,
      fontFace: BODY, fontSize: 11, bold: true, charSpacing: 1.4, color: r[3],
    });
    s.addText(r[1], {
      x: x + 0.3, y: y + 0.86, w: cw - 0.6, h: 0.32, isTextBox: true, margin: 0,
      fontFace: HEAD, fontSize: 16, bold: true, color: NAVY,
    });
    s.addText(
      r[2].map((t, j) => ({ text: t, options: { bullet: true, breakLine: j < r[2].length - 1 } })),
      {
        x: x + 0.3, y: y + 1.3, w: cw - 0.6, h: h - 1.58, isTextBox: true, margin: 0,
        valign: 'top', fontFace: BODY, fontSize: 12, color: INK,
        lineSpacingMultiple: 1.06, paraSpaceAfter: 7,
      },
    );
  });

  s.addText('Enforced twice: role middleware gates the portal, and a policy proves ownership of the individual record.', {
    x: M, y: 6.4, w: CW, h: 0.4, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 13, italic: true, color: MUTED,
  });

  s.addNotes([
    "[2:05-2:45] The three portals. 40 seconds.",
    "",
    "One application, three portals, and the separation is strict.",
    "",
    "The administrator is the registry: academic structure, accounts, rosters, every report.",
    "",
    "The lecturer sees only their own classes. They generate a term of sessions from the timetable in one action, mark the register, project the QR code, close the session.",
    "",
    "The student sees only their own record - their percentage, a warning below 75%, and their history.",
    "",
    "The line at the bottom matters. Access is checked twice. Middleware asks \"is this the right kind of user for this area\". A policy asks \"is this the right individual for this record\". Without the second check, one lecturer could edit another's register just by changing the number in the URL.",
  ].join('\n'));
}

// ===========================================================================
// 5 - TECHNOLOGY
// ===========================================================================
{
  const s = newSlide(false);
  heading(s, 'Built on Laravel 13 and MySQL', 'Every choice made against one constraint: it has to run on a stock XAMPP installation.');

  const tech = [
    ['PHP 8.5', 'Backed enumerations model the attendance states.'],
    ['Laravel 13', 'ORM, routing, validation, hashing, CSRF protection.'],
    ['MariaDB 10.4', 'MySQL-compatible, and the database that ships with XAMPP.'],
    ['Bootstrap 5.3', 'Responsive interface, loaded from a CDN.'],
    ['endroid/qr-code', 'QR generation in pure PHP, emitting SVG.'],
    ['PHPUnit 12', 'Automated testing against in-memory SQLite.'],
  ];

  const lw = 6.86;
  tech.forEach((t, i) => {
    const y = 1.88 + i * 0.72;
    s.addShape(pres.ShapeType.roundRect, {
      x: M, y, w: lw, h: 0.62, rectRadius: 0.04,
      fill: { color: i % 2 ? WHITE : TINT }, line: { color: LINE, width: 1 },
    });
    s.addText(t[0], {
      x: M + 0.24, y: y + 0.15, w: 1.92, h: 0.34, isTextBox: true, margin: 0,
      fontFace: HEAD, fontSize: 13.5, bold: true, color: NAVY,
    });
    s.addText(t[1], {
      x: M + 2.22, y: y + 0.16, w: lw - 2.46, h: 0.34, isTextBox: true, margin: 0,
      fontFace: BODY, fontSize: 11.5, color: MUTED,
    });
  });

  const rx = M + lw + 0.42, rw = W - M - rx;
  s.addShape(pres.ShapeType.roundRect, {
    x: rx, y: 1.88, w: rw, h: 2.24, rectRadius: 0.06,
    fill: { color: NAVY }, line: { color: NAVY, width: 0 },
  });
  s.addText('NO BUILD STEP', {
    x: rx + 0.3, y: 2.12, w: rw - 0.6, h: 0.26, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 10, bold: true, charSpacing: 1.6, color: GOLD,
  });
  s.addText('No Node. No npm.', {
    x: rx + 0.3, y: 2.44, w: rw - 0.6, h: 0.42, isTextBox: true, margin: 0,
    fontFace: HEAD, fontSize: 21, bold: true, color: WHITE,
  });
  s.addText('Bootstrap comes from a CDN, so the system runs after composer install, a migration and artisan serve. Nothing to compile.', {
    x: rx + 0.3, y: 2.94, w: rw - 0.6, h: 1.0, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 11.5, color: 'B9C6DB', lineSpacingMultiple: 1.14,
  });

  card(s, {
    x: rx, y: 4.3, w: rw, h: 1.92,
    kicker: 'A CONSTRAINT THAT SHAPED A CHOICE',
    kickerColor: AMBER,
    title: 'No gd, no imagick',
    body: 'Neither PHP image extension is installed, which rules out PNG entirely. The QR library was chosen because it emits SVG in pure PHP - and vector turned out sharper on a projector anyway.',
    titleSize: 15, bodySize: 11.5,
  });

  s.addNotes([
    "[2:45-3:20] Technology. 35 seconds.",
    "",
    "Laravel 13 on PHP 8.5, with MariaDB through XAMPP. I leaned on the framework for the security primitives specifically - hashing, CSRF, parameter binding - because hand-rolling those is where student projects get vulnerabilities.",
    "",
    "Two things on the right. There's no build step: Bootstrap loads from a CDN, so no Node, no npm, no compile. It runs anywhere after composer install and a migration.",
    "",
    "And below it - this machine has neither PHP image extension, which rules out generating the QR as a PNG. So I chose a library that emits SVG in pure PHP, and vector is actually sharper on a projector. The constraint improved the result.",
  ].join('\n'));
}
