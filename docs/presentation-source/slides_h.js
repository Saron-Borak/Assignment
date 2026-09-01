const D = require('./deck.js');
const {
  pres, newSlide, heading, statusDots, card, stat,
  NAVY, NAVY_2, GOLD, INK, MUTED, LINE, TINT, WHITE,
  GREEN, AMBER, RED, SLATE, HEAD, BODY, W, Hh, M, CW,
} = D;

// ===========================================================================
// 14 - DEFECTS FOUND
// ===========================================================================
{
  const s = newSlide(false);
  heading(s, 'Three defects the tests missed', 'All three were silent. None of them raised an error, which is exactly why they are worth reporting.');

  const defects = [
    ['1', 'Counts rendering blank', 'A select() call placed after withCount() replaced the counting subquery instead of adding to it, so several columns rendered empty.',
      'The page returned 200 and the layout was intact. Only a number was missing, so no test that checks the page loads could see it.', RED],
    ['2', 'Ambiguous column', 'An unqualified column name became ambiguous once a second table was joined, and the roster screen returned a 500.',
      'That screen had no test at the time. Found by a test that now walks every screen in all three portals.', AMBER],
    ['3', 'Wrong clock', 'A check-in at 10:28 for an 08:00 class was recorded as present. The app ran in UTC while the timetable stores local time.',
      'Every test builds times relative to now, so they stay self-consistent in any timezone. The tests could not detect it.', RED],
  ];

  const cw = (CW - 0.64) / 3;
  defects.forEach((d, i) => {
    const x = M + i * (cw + 0.32);
    s.addShape(pres.ShapeType.roundRect, {
      x, y: 1.9, w: cw, h: 4.06, rectRadius: 0.05,
      fill: { color: TINT }, line: { color: LINE, width: 1 },
    });
    s.addShape(pres.ShapeType.ellipse, {
      x: x + 0.28, y: 2.14, w: 0.42, h: 0.42, fill: { color: d[4] },
    });
    s.addText(d[0], {
      x: x + 0.28, y: 2.185, w: 0.42, h: 0.34, isTextBox: true, margin: 0,
      align: 'center', fontFace: HEAD, fontSize: 14, bold: true, color: WHITE,
    });
    s.addText(d[1], {
      x: x + 0.28, y: 2.7, w: cw - 0.56, h: 0.6, isTextBox: true, margin: 0,
      fontFace: HEAD, fontSize: 15.5, bold: true, color: NAVY, lineSpacingMultiple: 1.0,
    });
    s.addText(d[2], {
      x: x + 0.28, y: 3.34, w: cw - 0.56, h: 1.1, isTextBox: true, margin: 0,
      fontFace: BODY, fontSize: 11.5, color: INK, lineSpacingMultiple: 1.08,
    });
    s.addText('WHY THE TESTS MISSED IT', {
      x: x + 0.28, y: 4.54, w: cw - 0.56, h: 0.24, isTextBox: true, margin: 0,
      fontFace: BODY, fontSize: 9, bold: true, charSpacing: 1.2, color: AMBER,
    });
    s.addText(d[3], {
      x: x + 0.28, y: 4.82, w: cw - 0.56, h: 0.98, isTextBox: true, margin: 0,
      fontFace: BODY, fontSize: 11, color: MUTED, lineSpacingMultiple: 1.08,
    });
  });

  s.addText('All three were found by using the system rather than by testing it. That is an argument for doing both.', {
    x: M, y: 6.18, w: CW, h: 0.44, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 13.5, italic: true, bold: true, color: NAVY,
  });

  s.addNotes([
    "[11:25-12:20] Defects. 55 seconds. This slide earns credibility - don't rush it.",
    "",
    "I want to show you three defects rather than claim there were none, because how I found them is the point. All three were silent. Not one raised an error.",
    "",
    "First: calling select after withCount threw away the counting subquery, so several screens showed blank cells. The page returned 200, the layout was perfect, one number was missing. No test that checks the page loads could catch that.",
    "",
    "Second: a 500 from an ambiguous column name once a join was added. That screen had no test. It does now - and so does every other screen.",
    "",
    "Third is my favourite. A check-in at half ten for an eight o'clock class was recorded present, not late - the app ran in UTC while the timetable stores local time. My tests could not have found this: they build times relative to now, so they stay self-consistent in any timezone and pass regardless.",
    "",
    "The lesson: all three were found by using the system, not testing it. You need both.",
  ].join('\n'));
}

// ===========================================================================
// 15 - CHALLENGES
// ===========================================================================
{
  const s = newSlide(false);
  heading(s, 'What the constraints taught me', 'The limitations shaped the design more than the requirements did.');

  const rows = [
    ['No image extension available', 'Ruled out PNG entirely, so I chose a library that emits SVG in pure PHP. Vector output is sharper on a projector and needs no second request. The constraint improved the result.', GREEN],
    ['A photograph defeats a static code', 'Forced the rotating-token design, and forced me to name the trade-off: a rejected scan during rotation, in exchange for a guarantee that actually holds.', GOLD],
    ['MySQL rejected an index name', 'The first migration failed on a 64-character identifier limit. Lesson: run migrations against the real database early, rather than trusting a schema that merely looks correct.', SLATE],
    ['Three write paths, one set of rules', 'Drove the service layer. When the closing rule changed late in the project, it was one edit instead of three, and none of them could be forgotten.', GREEN],
  ];

  rows.forEach((r, i) => {
    const y = 1.94 + i * 1.14;
    s.addShape(pres.ShapeType.ellipse, {
      x: M, y: y + 0.14, w: 0.44, h: 0.44, fill: { color: r[2] },
    });
    s.addText(String(i + 1), {
      x: M, y: y + 0.185, w: 0.44, h: 0.36, isTextBox: true, margin: 0,
      align: 'center', fontFace: HEAD, fontSize: 15, bold: true,
      color: r[2] === GOLD ? NAVY : WHITE,
    });
    s.addText(r[0], {
      x: M + 0.68, y, w: CW - 0.68, h: 0.36, isTextBox: true, margin: 0,
      fontFace: HEAD, fontSize: 17, bold: true, color: NAVY,
    });
    s.addText(r[1], {
      x: M + 0.68, y: y + 0.38, w: CW - 0.68, h: 0.62, isTextBox: true, margin: 0,
      fontFace: BODY, fontSize: 12.5, color: MUTED, lineSpacingMultiple: 1.1,
    });
  });

  s.addNotes([
    "[12:20-12:55] Challenges. 35 seconds. Move briskly.",
    "",
    "Four genuine challenges, and a pattern across them.",
    "",
    "No image extension, which pushed me to SVG - and SVG beat the PNG I originally wanted.",
    "",
    "The static QR problem, which drove the rotation design and forced me to be explicit about the trade-off rather than pretend there wasn't one.",
    "",
    "A migration that failed on an index name being too long, which taught me to test against the real database rather than assume a schema that reads correctly will apply.",
    "",
    "And three write paths, which produced the service layer.",
    "",
    "The pattern is the point: in every case the constraint made the design better than the version I had in mind before I hit it.",
  ].join('\n'));
}

// ===========================================================================
// 16 - FUTURE SCOPE
// ===========================================================================
{
  const s = newSlide(false);
  heading(s, 'What I would build next', 'Ordered by value against effort. The first three are close to free.');

  const groups = [
    ['READY TO BUILD', GREEN, [
      ['Automatic notification', 'Email or SMS when a student crosses below the threshold. The calculation exists; only delivery is missing.'],
      ['Dashboard charts', 'Attendance trends over the term, from data the reporting service already returns.'],
      ['PDF reports', 'Formal records. Currently covered by print stylesheets through the browser.'],
    ]],
    ['MEANINGFUL WORK', AMBER, [
      ['Attendance appeals', 'A student requests an absence be reclassified as excused, with lecturer approval.'],
      ['Timetable clash detection', 'Warn when a lecturer, room or student is double-booked.'],
      ['Location-verified check-in', 'Require the request to come from campus, closing the last gap where a live code is shared.'],
    ]],
    ['LARGER PROJECTS', SLATE, [
      ['Native mobile app', 'A dedicated scanner. The responsive interface already covers this adequately.'],
      ['Biometric attendance', 'Eliminates proxy attendance entirely, but needs hardware in every room.'],
      ['Multi-campus support', 'Several campuses with their own calendars and timezones.'],
    ]],
  ];

  const cw = (CW - 0.64) / 3;
  groups.forEach((g, i) => {
    const x = M + i * (cw + 0.32);
    s.addShape(pres.ShapeType.roundRect, {
      x, y: 1.9, w: cw, h: 4.24, rectRadius: 0.05,
      fill: { color: TINT }, line: { color: LINE, width: 1 },
    });
    s.addShape(pres.ShapeType.ellipse, {
      x: x + 0.28, y: 2.16, w: 0.26, h: 0.26, fill: { color: g[1] },
    });
    s.addText(g[0], {
      x: x + 0.66, y: 2.16, w: cw - 0.94, h: 0.28, isTextBox: true, margin: 0,
      fontFace: BODY, fontSize: 10, bold: true, charSpacing: 1.4, color: g[1],
    });
    g[2].forEach((it, j) => {
      const y = 2.66 + j * 1.16;
      s.addText(it[0], {
        x: x + 0.28, y, w: cw - 0.56, h: 0.3, isTextBox: true, margin: 0,
        fontFace: HEAD, fontSize: 13.5, bold: true, color: NAVY,
      });
      s.addText(it[1], {
        x: x + 0.28, y: y + 0.3, w: cw - 0.56, h: 0.78, isTextBox: true, margin: 0,
        fontFace: BODY, fontSize: 11, color: MUTED, lineSpacingMultiple: 1.08,
      });
    });
  });

  s.addNotes([
    "[12:55-13:25] Future scope. 30 seconds - keep it short.",
    "",
    "Briefly, what comes next, ordered by value against effort.",
    "",
    "The left column is nearly free, because the hard part already exists. Notification is the clearest example: the system already knows exactly who is at risk. It just doesn't tell them yet. That's the highest-value thing I'd add, because it closes the loop on the original problem - the point was to act early, and right now someone still has to remember to read the report.",
    "",
    "The middle column is real but well-defined work. The right column needs hardware or a much bigger scope.",
  ].join('\n'));
}
