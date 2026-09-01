const D = require('./deck.js');
const {
  pres, newSlide, heading, statusDots, card, stat,
  NAVY, NAVY_2, GOLD, INK, MUTED, LINE, TINT, WHITE,
  GREEN, AMBER, RED, SLATE, HEAD, BODY, W, Hh, M, CW,
} = D;

// ===========================================================================
// 1 - TITLE
// ===========================================================================
{
  const s = newSlide(true);

  s.addText('EAST ASIA MANAGEMENT UNIVERSITY', {
    x: M, y: 1.45, w: CW, h: 0.32, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 13, bold: true, charSpacing: 2.4, color: GOLD,
  });

  s.addText('Student Attendance', {
    x: M, y: 1.92, w: 8.4, h: 0.86, isTextBox: true, margin: 0,
    fontFace: HEAD, fontSize: 50, bold: true, color: WHITE,
  });
  s.addText('Management System', {
    x: M, y: 2.72, w: 8.4, h: 0.86, isTextBox: true, margin: 0,
    fontFace: HEAD, fontSize: 50, bold: true, color: WHITE,
  });

  s.addText('A web platform that records attendance as the class happens, and makes the percentage visible while it still matters.', {
    x: M, y: 3.74, w: 7.9, h: 0.66, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 15, color: 'B9C6DB', lineSpacingMultiple: 1.15,
  });

  statusDots(s, M, 4.62, 0.17, true);
  s.addText('Present   .   Late   .   Absent   .   Excused', {
    x: M + 1.32, y: 4.56, w: 5.0, h: 0.3, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 11, color: '7D8DA8',
  });

  s.addText([
    { text: 'Final Project Presentation', options: { bold: true, breakLine: true } },
    { text: '[ Your Name ]   .   [ Student ID ]', options: { breakLine: true } },
    { text: '[ Course Code ]   .   [ Date ]', options: {} },
  ], {
    x: M, y: 5.38, w: 6.0, h: 1.1, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 12.5, color: '9DAAC2', lineSpacingMultiple: 1.25,
  });

  const px = 9.05, pw = 3.58;
  s.addShape(pres.ShapeType.roundRect, {
    x: px, y: 1.92, w: pw, h: 3.62, rectRadius: 0.06,
    fill: { color: NAVY_2 }, line: { color: '2C4574', width: 1 },
  });
  s.addText('THE SYSTEM TODAY', {
    x: px + 0.32, y: 2.18, w: pw - 0.64, h: 0.26, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 9.5, bold: true, charSpacing: 1.6, color: GOLD,
  });
  const rows = [['92', 'routes'], ['14', 'database tables'], ['73', 'automated tests'], ['6,145', 'attendance records seeded']];
  rows.forEach((r, i) => {
    const y = 2.58 + i * 0.71;
    s.addText(r[0], {
      x: px + 0.32, y, w: 1.25, h: 0.42, isTextBox: true, margin: 0,
      fontFace: HEAD, fontSize: 24, bold: true, color: WHITE,
    });
    s.addText(r[1], {
      x: px + 1.62, y: y + 0.10, w: pw - 1.94, h: 0.4, isTextBox: true, margin: 0,
      fontFace: BODY, fontSize: 11, color: '9DAAC2',
    });
  });

  s.addNotes([
    "[0:00-0:25] Opening.",
    "",
    "Good morning. My project is a Student Attendance Management System for East Asia Management University.",
    "",
    "In one line: it records attendance at the moment a class happens, and makes each student's percentage visible while there is still time to act on it.",
    "",
    "I'll spend about seven minutes on the problem and the design, three minutes demonstrating it live, and the rest on testing and what I learned.",
  ].join('\n'));
}
