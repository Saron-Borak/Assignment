const D = require('./deck.js');
const {
  pres, newSlide, heading, statusDots, card, stat,
  NAVY, NAVY_2, GOLD, INK, MUTED, LINE, TINT, WHITE,
  GREEN, AMBER, RED, SLATE, HEAD, BODY, W, Hh, M, CW,
} = D;

// ===========================================================================
// 2 - THE PROBLEM
// ===========================================================================
{
  const s = newSlide(false);
  heading(s, 'The problem with a paper register',
    'Four failures, all consequences of one thing: the data is captured on paper and totalled far too late.');

  const cards = [
    ['1', 'Percentages arrive too late', 'Totals are added up at the end of the semester. Nobody discovers a student has crossed the line until the moment it is enforced.', RED],
    ['2', 'Roll-call costs teaching time', 'Calling thirty names takes several minutes out of every single session, across every class, all term.', AMBER],
    ['3', 'Sheets are lost and signed for', 'A sheet passed around the room can be signed by a friend. A mislaid sheet destroys the record entirely.', RED],
    ['4', 'No single source of truth', 'The registry, the lecturer and the student can each hold a different version of the same record.', SLATE],
  ];

  // Badge sits above the title rather than beside it, so a long title can
  // never run into it.
  const cw = (CW - 0.4) / 2, ch = 2.0;
  cards.forEach((c, i) => {
    const x = M + (i % 2) * (cw + 0.4);
    const y = 1.80 + Math.floor(i / 2) * (ch + 0.3);

    s.addShape(pres.ShapeType.roundRect, {
      x, y, w: cw, h: ch, rectRadius: 0.05,
      fill: { color: TINT }, line: { color: LINE, width: 1 },
    });
    s.addShape(pres.ShapeType.ellipse, {
      x: x + 0.28, y: y + 0.24, w: 0.42, h: 0.42, fill: { color: c[3] },
    });
    s.addText(c[0], {
      x: x + 0.28, y: y + 0.285, w: 0.42, h: 0.34, isTextBox: true, margin: 0,
      align: 'center', fontFace: HEAD, fontSize: 14, bold: true, color: WHITE,
    });
    s.addText(c[1], {
      x: x + 0.86, y: y + 0.3, w: cw - 1.14, h: 0.34, isTextBox: true, margin: 0,
      fontFace: HEAD, fontSize: 16.5, bold: true, color: NAVY,
    });
    s.addText(c[2], {
      x: x + 0.28, y: y + 0.86, w: cw - 0.56, h: ch - 1.06, isTextBox: true, margin: 0,
      valign: 'top', fontFace: BODY, fontSize: 12.5, color: INK,
      lineSpacingMultiple: 1.1,
    });
  });

  s.addText('The university enforces a 75% rule using data that arrives after the decision has already been made.', {
    x: M, y: 6.3, w: CW, h: 0.44, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 14, italic: true, bold: true, color: NAVY,
  });

  s.addNotes([
    "[0:25-1:25] The problem. 60 seconds.",
    "",
    "The university already records attendance on paper, and already enforces a 75% rule. So the problem isn't that attendance is unmeasured. It's that the measurement is useless by the time anyone sees it.",
    "",
    "Four failures. First and most important: totals are added up at the end of term, so a student failing the requirement finds out when they're barred from the exam, not while they can still fix it.",
    "",
    "Second, roll-call costs minutes out of every session, all semester.",
    "",
    "Third, a sheet passed around a room is trivially signed by a friend, and a lost sheet loses the session outright.",
    "",
    "Fourth, three parties hold three versions, with no way to reconcile them.",
    "",
    "So the rule is real, but the data behind it is late and not trusted.",
  ].join('\n'));
}

// ===========================================================================
// 3 - OBJECTIVES
// ===========================================================================
{
  const s = newSlide(false);
  heading(s, 'What the system had to do', 'Six objectives, fixed at the start of the project. All six were delivered.');

  s.addShape(pres.ShapeType.roundRect, {
    x: M, y: 1.86, w: 3.5, h: 3.94, rectRadius: 0.06,
    fill: { color: NAVY }, line: { color: NAVY, width: 0 },
  });
  s.addText('THE RULE', {
    x: M + 0.34, y: 2.14, w: 2.82, h: 0.26, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 10, bold: true, charSpacing: 1.8, color: GOLD,
  });
  s.addText('75%', {
    x: M + 0.34, y: 2.50, w: 2.82, h: 1.26, isTextBox: true, margin: 0,
    fontFace: HEAD, fontSize: 72, bold: true, color: WHITE,
  });
  s.addText('minimum attendance in every class a student is enrolled in.', {
    x: M + 0.34, y: 3.84, w: 2.82, h: 0.76, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 13, color: 'B9C6DB', lineSpacingMultiple: 1.15,
  });
  s.addText('Fall below it and you may be barred from the final examination.', {
    x: M + 0.34, y: 4.68, w: 2.82, h: 0.82, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 11.5, italic: true, color: '8E9EB8', lineSpacingMultiple: 1.15,
  });

  const items = [
    ['Model the university', 'A normalised schema for faculties, courses, semesters, rosters and every attendance event.'],
    ['Separate the three roles', 'Administrator, lecturer and student, each restricted to the data they are entitled to see.'],
    ['Make marking fast', 'A full register completed in seconds rather than minutes.'],
    ['Remove roll-call entirely', 'Self check-in by QR code, resistant to a student checking in an absent friend.'],
    ['Calculate continuously', 'Percentages against the 75% rule, with at-risk students surfaced early enough to act.'],
    ['Prove it works', 'Automated tests covering authorisation, data integrity and the percentage arithmetic.'],
  ];

  const lx = M + 3.5 + 0.44;
  const lw = W - M - lx;
  items.forEach((it, i) => {
    const y = 1.88 + i * 0.665;
    s.addShape(pres.ShapeType.ellipse, {
      x: lx, y: y + 0.03, w: 0.34, h: 0.34, fill: { color: TINT }, line: { color: LINE, width: 1 },
    });
    s.addText(String(i + 1), {
      x: lx, y: y + 0.065, w: 0.34, h: 0.28, isTextBox: true, margin: 0,
      align: 'center', fontFace: HEAD, fontSize: 12, bold: true, color: NAVY,
    });
    s.addText(it[0], {
      x: lx + 0.5, y, w: lw - 0.5, h: 0.28, isTextBox: true, margin: 0,
      fontFace: HEAD, fontSize: 14.5, bold: true, color: NAVY,
    });
    s.addText(it[1], {
      x: lx + 0.5, y: y + 0.27, w: lw - 0.5, h: 0.38, isTextBox: true, margin: 0,
      fontFace: BODY, fontSize: 11.5, color: MUTED, lineSpacingMultiple: 1.0,
    });
  });

  s.addNotes([
    "[1:25-2:05] Objectives. 40 seconds.",
    "",
    "Everything here serves one rule, on the left: 75% attendance in every class, and falling below it can bar you from the exam.",
    "",
    "From that, six objectives. Model the university properly. Separate the three roles. Make marking fast enough that a lecturer will actually use it. Remove roll-call altogether with self check-in - and make that resistant to cheating, which was the hardest part. Calculate continuously rather than at the end. And prove it works with tests.",
    "",
    "All six were delivered. I'll come back to the two that were genuinely difficult: the check-in, and the arithmetic.",
  ].join('\n'));
}
