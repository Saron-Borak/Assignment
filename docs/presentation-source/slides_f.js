const D = require('./deck.js');
const {
  pres, newSlide, heading, statusDots, card, stat,
  NAVY, NAVY_2, GOLD, INK, MUTED, LINE, TINT, WHITE,
  GREEN, AMBER, RED, SLATE, HEAD, BODY, W, Hh, M, CW,
} = D;

// ===========================================================================
// 10 - THE PERCENTAGE RULE
// ===========================================================================
{
  const s = newSlide(false);
  heading(s, 'What counts, and what does not', 'The whole system turns on one calculation, so it is defined in exactly one method.');

  // The formula
  s.addShape(pres.ShapeType.roundRect, {
    x: M, y: 1.9, w: CW, h: 1.24, rectRadius: 0.06,
    fill: { color: NAVY }, line: { color: NAVY, width: 0 },
  });
  s.addText('attended', {
    x: M + 0.5, y: 2.16, w: 2.3, h: 0.34, isTextBox: true, margin: 0,
    align: 'center', fontFace: 'Courier New', fontSize: 15, bold: true, color: '7FD1A0',
  });
  s.addShape(pres.ShapeType.line, {
    x: M + 0.5, y: 2.56, w: 2.3, h: 0, line: { color: '5B6B85', width: 1.25 },
  });
  s.addText('countable', {
    x: M + 0.5, y: 2.62, w: 2.3, h: 0.34, isTextBox: true, margin: 0,
    align: 'center', fontFace: 'Courier New', fontSize: 15, bold: true, color: WHITE,
  });
  s.addText('x 100', {
    x: M + 2.94, y: 2.38, w: 0.9, h: 0.36, isTextBox: true, margin: 0,
    fontFace: 'Courier New', fontSize: 15, color: '9DAAC2',
  });
  s.addText([
    { text: 'attended', options: { bold: true, color: '7FD1A0', breakLine: true } },
    { text: 'present, plus late  (late still means you were in the room)', options: { color: 'B9C6DB', breakLine: true } },
    { text: 'countable', options: { bold: true, color: WHITE, breakLine: true } },
    { text: 'closed sessions, minus excused absences', options: { color: 'B9C6DB' } },
  ], {
    x: M + 4.3, y: 2.14, w: CW - 4.8, h: 0.86, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 12.5, lineSpacingMultiple: 1.16,
  });

  const decisions = [
    ['Open sessions do not count', 'A class that is still running, or has not happened yet, is excluded. A lecturer can open a session without instantly penalising anyone who has not arrived.', GREEN],
    ['Excused leaves the denominator', 'Approved absence is not counted against the student at all - it is removed from the total rather than recorded as a miss.', SLATE],
    ['The threshold is configuration', 'The 75% figure lives in a config file, not in the code. The university can change its own rule without a developer.', GOLD],
  ];

  const cw = (CW - 0.64) / 3;
  decisions.forEach((d, i) => {
    const x = M + i * (cw + 0.32);
    card(s, {
      x, y: 3.44, w: cw, h: 2.08,
      title: d[0], body: d[2] ? d[1] : d[1], titleSize: 15, bodySize: 12,
    });
    s.addShape(pres.ShapeType.ellipse, {
      x: x + cw - 0.62, y: 3.66, w: 0.3, h: 0.3, fill: { color: d[2] },
    });
  });

  s.addText('A class that has not met yet returns zero rather than dividing by zero, and is not flagged as at risk. That is a test case, not an accident.', {
    x: M, y: 5.78, w: CW, h: 0.44, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 12.5, italic: true, color: MUTED,
  });

  s.addNotes([
    "[6:25-7:10] The percentage rule. 45 seconds.",
    "",
    "This is the calculation the whole system exists to produce.",
    "",
    "Attended is present plus late, because a late student was still in the room. That's configurable if the university disagrees.",
    "",
    "Countable is the interesting half: closed sessions minus excused absences.",
    "",
    "Three decisions there. First, only closed sessions count - a session open right now, or scheduled for next week, is excluded. That's what lets a lecturer open a register without everyone briefly showing as absent.",
    "",
    "Second, an excused absence leaves the denominator entirely. It's not a forgiven miss; it's a session that never counted.",
    "",
    "Third, the 75% is configuration, not code.",
    "",
    "And the edge case at the bottom: a class that hasn't met returns zero and is explicitly not at risk - otherwise everyone would look like they were failing on day one.",
  ].join('\n'));
}

// ===========================================================================
// 11 - REPORTING AT SCALE
// ===========================================================================
{
  const s = newSlide(false);
  heading(s, 'The report that would have collapsed', 'The obvious implementation works in a demonstration and fails in a real university.');

  const bw = (CW - 0.5) / 2;

  card(s, {
    x: M, y: 1.94, w: bw, h: 2.34,
    kicker: 'THE OBVIOUS WAY',
    kickerColor: RED,
    title: 'Loop the roster, query each student',
    body: 'Thirty students in a class means thirty queries. A university-wide report across sixty students and ten sections means hundreds. The cost grows with enrollment, so it looks fine with test data and falls over in use.',
    titleSize: 15.5, bodySize: 12, fill: 'FDF4F3', border: 'EBC7C2',
  });

  card(s, {
    x: M + bw + 0.5, y: 1.94, w: bw, h: 2.34,
    kicker: 'WHAT THE SYSTEM DOES',
    kickerColor: GREEN,
    title: 'One grouped query, conditional sums',
    body: 'SQL produces every counter in a single pass using SUM(CASE WHEN ...). Only the final division happens in PHP. The cost is flat no matter how many students are on the roster.',
    titleSize: 15.5, bodySize: 12, fill: 'F2F9F5', border: 'BFDCCB',
  });

  const figs = [
    ['30', 'students on the roster', NAVY],
    ['1', 'query to summarise all of them', GREEN],
    ['73', 'tests, one of which asserts exactly that', NAVY],
  ];
  const fw = (CW - 0.64) / 3;
  figs.forEach((f, i) => {
    const x = M + i * (fw + 0.32);
    s.addShape(pres.ShapeType.roundRect, {
      x, y: 4.6, w: fw, h: 1.4, rectRadius: 0.05,
      fill: { color: TINT }, line: { color: LINE, width: 1 },
    });
    s.addText(f[0], {
      x: x + 0.28, y: 4.76, w: fw - 0.56, h: 0.66, isTextBox: true, margin: 0,
      fontFace: HEAD, fontSize: 40, bold: true, color: f[2],
    });
    s.addText(f[1], {
      x: x + 0.28, y: 5.44, w: fw - 0.56, h: 0.44, isTextBox: true, margin: 0,
      fontFace: BODY, fontSize: 11.5, color: MUTED, lineSpacingMultiple: 1.05,
    });
  });

  s.addText('The test counts the queries, so this property cannot regress without the build failing.', {
    x: M, y: 6.2, w: CW, h: 0.4, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 12.5, italic: true, color: NAVY,
  });

  s.addNotes([
    "[7:10-7:45] Reporting performance. 35 seconds.",
    "",
    "One more design point, then the demo.",
    "",
    "The natural way to build this report is to loop the students and calculate each one. Thirty students, thirty queries. University-wide, hundreds. It grows with enrollment - so it works in a demo with five students and falls over with a real cohort.",
    "",
    "Instead every report is a single grouped query using conditional sums. SQL produces all the counters in one pass; PHP only does the final division. Thirty students and three hundred cost the same.",
    "",
    "And there's a test that counts the queries and fails if it's more than one. So this can't quietly regress - reintroduce a loop and the build breaks.",
  ].join('\n'));
}
