const D = require('./deck.js');
const {
  pres, newSlide, heading, statusDots, card, stat,
  NAVY, NAVY_2, GOLD, INK, MUTED, LINE, TINT, WHITE,
  GREEN, AMBER, RED, SLATE, HEAD, BODY, W, Hh, M, CW,
} = D;

// ===========================================================================
// 12 - LIVE DEMONSTRATION  (dark divider)
// ===========================================================================
{
  const s = newSlide(true);

  s.addText('LIVE DEMONSTRATION', {
    x: M, y: 1.5, w: CW, h: 0.34, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 13, bold: true, charSpacing: 2.6, color: GOLD,
  });
  s.addText('Let me show you the system', {
    x: M, y: 1.95, w: 7.6, h: 0.78, isTextBox: true, margin: 0,
    fontFace: HEAD, fontSize: 40, bold: true, color: WHITE,
  });
  s.addText('Running against seeded data: 60 students, 10 class sections, a full term of attendance history.', {
    x: M, y: 2.82, w: 7.6, h: 0.5, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 14, color: 'B9C6DB', lineSpacingMultiple: 1.12,
  });

  const steps = [
    ['Administrator', 'Dashboard figures, then the at-risk report', GOLD],
    ['Lecturer', 'Open today session, project the QR code', GREEN],
    ['Student', 'Check in with the 6-character code', GOLD],
    ['Lecturer', 'Mark the register, then close it', GREEN],
    ['Student', 'The updated percentage and the warning', GOLD],
    ['Access control', 'A student pasting an admin URL', RED],
  ];
  steps.forEach((st, i) => {
    const x = M + (i % 2) * ((CW) / 2 + 0.16);
    const y = 3.62 + Math.floor(i / 2) * 0.82;
    s.addShape(pres.ShapeType.ellipse, {
      x, y: y + 0.06, w: 0.3, h: 0.3, fill: { color: st[2] },
    });
    s.addText(String(i + 1), {
      x, y: y + 0.095, w: 0.3, h: 0.26, isTextBox: true, margin: 0,
      align: 'center', fontFace: HEAD, fontSize: 11, bold: true,
      color: st[2] === GOLD ? NAVY : WHITE,
    });
    s.addText(st[0], {
      x: x + 0.46, y, w: 2.3, h: 0.3, isTextBox: true, margin: 0,
      fontFace: HEAD, fontSize: 14, bold: true, color: WHITE,
    });
    s.addText(st[1], {
      x: x + 0.46, y: y + 0.29, w: (CW / 2) - 0.62, h: 0.4, isTextBox: true, margin: 0,
      fontFace: BODY, fontSize: 11.5, color: '9DAAC2', lineSpacingMultiple: 1.05,
    });
  });

  s.addNotes([
    "[7:45-10:45] LIVE DEMONSTRATION. Three minutes. Keep moving.",
    "",
    "BEFORE YOU START - have all of this ready:",
    "  - Server already running: php artisan serve",
    "  - Two browsers (or one normal + one private window), so you can be lecturer and student at once",
    "  - Browser 1 signed in as admin@eamu.edu / password",
    "  - Browser 2 sitting on the login page",
    "  - If the data has been clicked around: php artisan migrate:fresh --seed",
    "",
    "RUNNING ORDER:",
    "",
    "1. ADMIN (35s). Dashboard - 60 students, 10 sections, 91.1% overall. Then the at-risk report: seven students below 75%, worst first. Say: this is the number the paper system could not produce until it was too late.",
    "",
    "2. LECTURER (40s). Sign in as c.nou@eamu.edu. Point out only two classes - the scoping is real. Open today's session. The projection screen appears: QR code, six-character code, countdown, zero of thirty checked in.",
    "",
    "3. STUDENT (35s). Second browser, sign in as kosal.tep@student.eamu.edu. Note the green banner that detected the open session. Type the six-character code. Confirmation: checked in as present.",
    "",
    "4. BACK TO LECTURER (35s). Refresh the projection - the counter has moved. Open the marking register: thirty students, and the one who checked in carries a \"Code self check-in\" badge.",
    "",
    "5. CLOSE IT (20s). Twenty-nine marked absent automatically; the self check-in preserved as present.",
    "",
    "6. ACCESS CONTROL (15s). As the student, paste an /admin URL. Access denied page.",
    "",
    "IF THE DEMO FAILS: do not debug on stage. Say \"I have screenshots of this in the report\", move to the testing slide, and recover afterwards.",
  ].join('\n'));
}

// ===========================================================================
// 13 - TESTING
// ===========================================================================
{
  const s = newSlide(false);
  heading(s, 'Tested, not just demonstrated', '73 automated tests running against real HTTP requests, not method calls.');

  const figs = [
    ['73', 'tests', NAVY],
    ['249', 'assertions', NAVY],
    ['2.9s', 'to run the suite', GREEN],
    ['50', 'documented test cases', GOLD],
  ];
  const fw = (CW - 0.96) / 4;
  figs.forEach((f, i) => {
    const x = M + i * (fw + 0.32);
    s.addShape(pres.ShapeType.roundRect, {
      x, y: 1.9, w: fw, h: 1.24, rectRadius: 0.05,
      fill: { color: TINT }, line: { color: LINE, width: 1 },
    });
    s.addText(f[0], {
      x: x + 0.24, y: 2.02, w: fw - 0.48, h: 0.62, isTextBox: true, margin: 0,
      fontFace: HEAD, fontSize: 36, bold: true, color: f[2],
    });
    s.addText(f[1], {
      x: x + 0.24, y: 2.66, w: fw - 0.48, h: 0.34, isTextBox: true, margin: 0,
      fontFace: BODY, fontSize: 11.5, color: MUTED,
    });
  });

  const suites = [
    ['Authentication', '9', 'Sign-in, role landing pages, deactivated accounts'],
    ['Role access', '9', 'Cross-portal refusals and the ownership policies'],
    ['Admin management', '10', 'CRUD, validation, bulk enrollment, password reset'],
    ['Attendance marking', '7', 'Register saving, roster tampering, close-marks-absent'],
    ['QR check-in', '14', 'Expired, rotated and unknown tokens; late window'],
    ['Reporting', '11', 'Percentage arithmetic, excused handling, query count'],
    ['CSV export', '6', 'Headers, encoding, per-role authorisation'],
    ['Page rendering', '7', 'Every screen in all three portals'],
  ];

  const tw = 7.3;
  suites.forEach((r, i) => {
    const y = 3.42 + i * 0.4;
    if (i % 2 === 0) {
      s.addShape(pres.ShapeType.rect, {
        x: M, y, w: tw, h: 0.4, fill: { color: TINT }, line: { color: TINT, width: 0 },
      });
    }
    s.addText(r[0], {
      x: M + 0.16, y: y + 0.07, w: 2.1, h: 0.28, isTextBox: true, margin: 0,
      fontFace: BODY, fontSize: 11.5, bold: true, color: NAVY,
    });
    s.addText(r[1], {
      x: M + 2.3, y: y + 0.07, w: 0.42, h: 0.28, isTextBox: true, margin: 0,
      align: 'right', fontFace: BODY, fontSize: 11.5, bold: true, color: GREEN,
    });
    s.addText(r[2], {
      x: M + 2.94, y: y + 0.07, w: tw - 3.1, h: 0.28, isTextBox: true, margin: 0,
      fontFace: BODY, fontSize: 11, color: MUTED,
    });
  });

  const rx = M + tw + 0.44, rw = W - M - rx;
  card(s, {
    x: rx, y: 3.42, w: rw, h: 1.72,
    kicker: 'A USEFUL SETTING',
    title: 'Lazy loading disabled in tests',
    body: 'Any relationship the code forgot to load in advance throws instead of quietly issuing an extra query per row - so an N+1 fails the build.',
    titleSize: 14, bodySize: 11.5,
  });
  card(s, {
    x: rx, y: 5.3, w: rw, h: 1.72,
    kicker: 'WHY REAL REQUESTS',
    kickerColor: GREEN,
    title: 'Routing to database, in one assertion',
    body: 'Tests go through the routes, so middleware, policies, validation, the service layer and the schema are all exercised together.',
    titleSize: 14, bodySize: 11.5,
  });

  s.addNotes([
    "[10:45-11:25] Testing. 40 seconds.",
    "",
    "Seventy-three tests, two hundred and forty-nine assertions, and the suite runs in under three seconds - which matters, because a slow suite is one you stop running.",
    "",
    "The important choice is on the right: tests go through the actual routes, not straight to method calls. So one assertion exercises routing, middleware, the ownership policies, validation, the service layer and the schema together.",
    "",
    "The other setting worth mentioning is lazy loading, which I have disabled in tests. If the code forgets to eager-load a relationship it throws instead of quietly firing an extra query per row - so an N+1 performance bug fails the build rather than shipping silently.",
    "",
    "The report documents fifty test cases individually, each traced to a numbered requirement.",
  ].join('\n'));
}
