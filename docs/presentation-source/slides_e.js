const D = require('./deck.js');
const {
  pres, newSlide, heading, statusDots, card, stat,
  NAVY, NAVY_2, GOLD, INK, MUTED, LINE, TINT, WHITE,
  GREEN, AMBER, RED, SLATE, HEAD, BODY, W, Hh, M, CW,
} = D;

// ===========================================================================
// 8 - QR CHECK-IN FLOW
// ===========================================================================
{
  const s = newSlide(false);
  heading(s, 'Self check-in, start to finish', 'The lecturer opens a session and projects a code. Everything after that is the students own phone.');

  const steps = [
    ['1', 'Lecturer opens the session', 'The system mints a 64-character token and a 6-character readable code, valid for 60 seconds.'],
    ['2', 'The code is projected', 'A full-screen display shows the QR code, the short code, a countdown and live check-in counters.'],
    ['3', 'Student scans it', 'The phone opens the check-in address. A signed-out student is sent to login and returned automatically.'],
    ['4', 'Four checks run', 'Token current? Session open? Student enrolled? Not already checked in? All four must pass.'],
    ['5', 'Present or late is decided', 'Arriving more than 15 minutes after the start time is recorded as late rather than present.'],
    ['6', 'Lecturer closes the register', 'Everyone still unmarked is recorded absent in a single statement, and the code is retired.'],
  ];

  const cw = (CW - 0.64) / 3, ch = 2.06;
  steps.forEach((st, i) => {
    const x = M + (i % 3) * (cw + 0.32);
    const y = 1.92 + Math.floor(i / 3) * (ch + 0.34);
    s.addShape(pres.ShapeType.roundRect, {
      x, y, w: cw, h: ch, rectRadius: 0.05,
      fill: { color: TINT }, line: { color: LINE, width: 1 },
    });
    s.addShape(pres.ShapeType.ellipse, {
      x: x + 0.28, y: y + 0.26, w: 0.42, h: 0.42,
      fill: { color: i === 3 ? GOLD : NAVY },
    });
    s.addText(st[0], {
      x: x + 0.28, y: y + 0.305, w: 0.42, h: 0.34, isTextBox: true, margin: 0,
      align: 'center', fontFace: HEAD, fontSize: 14, bold: true, color: WHITE,
    });
    s.addText(st[1], {
      x: x + 0.28, y: y + 0.82, w: cw - 0.56, h: 0.56, isTextBox: true, margin: 0,
      fontFace: HEAD, fontSize: 14.5, bold: true, color: NAVY, lineSpacingMultiple: 1.0,
    });
    s.addText(st[2], {
      x: x + 0.28, y: y + 1.36, w: cw - 0.56, h: 0.56, isTextBox: true, margin: 0,
      fontFace: BODY, fontSize: 11.5, color: MUTED, lineSpacingMultiple: 1.06,
    });
  });

  s.addText('A student with no working camera types the 6-character code instead. Same service, same four checks, same result.', {
    x: M, y: 6.44, w: CW, h: 0.4, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 13, italic: true, color: NAVY,
  });

  s.addNotes([
    "[4:50-5:35] The check-in flow. 45 seconds.",
    "",
    "Here's the whole flow - I'll demonstrate it shortly.",
    "",
    "The lecturer opens the session. The system issues two credentials: a long random token inside the QR code, and a six-character code a human can read. That gets projected. Students scan it with the phone camera - no app to install, it's just a URL.",
    "",
    "Step four is where the real work is. Four conditions must all hold: the token is current, the session is open, the student is actually enrolled, and they haven't already checked in. Any one fails and it's refused with a message written for the student.",
    "",
    "Step five decides present or late. Closing the register marks everyone else absent.",
    "",
    "Note the typed-code fallback - that's how I'll demo this without passing my phone around.",
  ].join('\n'));
}

// ===========================================================================
// 9 - STOPPING PROXY ATTENDANCE  (dark)
// ===========================================================================
{
  const s = newSlide(true);
  heading(s, 'The obvious version does not work', 'A static QR code defeats itself the first time a student photographs it.', true);

  const bw = (CW - 0.5) / 2;

  // Naive
  s.addShape(pres.ShapeType.roundRect, {
    x: M, y: 1.94, w: bw, h: 2.0, rectRadius: 0.06,
    fill: { color: '2A1F26' }, line: { color: '6E3A38', width: 1 },
  });
  s.addText('ONE CODE PER SESSION', {
    x: M + 0.32, y: 2.2, w: bw - 0.64, h: 0.26, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 10, bold: true, charSpacing: 1.6, color: 'E8908A',
  });
  s.addText('Photograph it, send it to a friend.', {
    x: M + 0.32, y: 2.54, w: bw - 0.64, h: 0.38, isTextBox: true, margin: 0,
    fontFace: HEAD, fontSize: 17, bold: true, color: WHITE,
  });
  s.addText('The first student to arrive can mark the entire class present, from anywhere. The system has now recorded a lie, and it looks exactly like the truth.', {
    x: M + 0.32, y: 3.0, w: bw - 0.64, h: 0.8, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 12, color: 'C9A9A6', lineSpacingMultiple: 1.14,
  });

  // Rotating
  s.addShape(pres.ShapeType.roundRect, {
    x: M + bw + 0.5, y: 1.94, w: bw, h: 2.0, rectRadius: 0.06,
    fill: { color: '172E22' }, line: { color: '2F6B47', width: 1 },
  });
  s.addText('A ROTATING TOKEN', {
    x: M + bw + 0.82, y: 2.2, w: bw - 0.64, h: 0.26, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 10, bold: true, charSpacing: 1.6, color: '7FD1A0',
  });
  s.addText('The photograph expires in 60 seconds.', {
    x: M + bw + 0.82, y: 2.54, w: bw - 0.64, h: 0.38, isTextBox: true, margin: 0,
    fontFace: HEAD, fontSize: 17, bold: true, color: WHITE,
  });
  s.addText('The projected page mints a new token every 45 seconds and the old one stops working. By the time a screenshot reaches anyone, it is already worthless.', {
    x: M + bw + 0.82, y: 3.0, w: bw - 0.64, h: 0.8, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 12, color: 'A8C7B4', lineSpacingMultiple: 1.14,
  });

  // Timeline of rotations
  s.addText('WHAT THE PROJECTOR SHOWS', {
    x: M, y: 4.28, w: 5.0, h: 0.26, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 10, bold: true, charSpacing: 1.6, color: GOLD,
  });
  const codes = ['UFV365', 'K92QHT', 'B4XMPD', 'R7TJW2'];
  codes.forEach((c, i) => {
    const x = M + i * 2.34;
    s.addShape(pres.ShapeType.roundRect, {
      x, y: 4.66, w: 1.98, h: 0.66, rectRadius: 0.05,
      fill: { color: i === 3 ? NAVY_2 : '16283F' },
      line: { color: i === 3 ? GOLD : '2C4574', width: i === 3 ? 1.5 : 1 },
    });
    s.addText(c, {
      x: x + 0.06, y: 4.79, w: 1.86, h: 0.36, isTextBox: true, margin: 0,
      align: 'center', fontFace: 'Courier New', fontSize: 16, bold: true,
      color: i === 3 ? GOLD : '8095B4',
    });
    s.addText(i === 3 ? 'live now' : `expired ${(3 - i) * 45}s ago`, {
      x: x + 0.06, y: 5.36, w: 1.86, h: 0.26, isTextBox: true, margin: 0,
      align: 'center', fontFace: BODY, fontSize: 9.5,
      color: i === 3 ? '7FD1A0' : '5B6B85',
    });
    if (i < 3) {
      s.addShape(pres.ShapeType.line, {
        x: x + 1.98, y: 4.99, w: 0.36, h: 0,
        line: { color: '3B537A', width: 1.25, endArrowType: 'triangle' },
      });
    }
  });

  s.addText('The trade-off I accepted: a scan landing exactly on a rotation is rejected and has to be repeated. Two seconds lost, versus a guarantee that holds.', {
    x: M, y: 5.94, w: CW, h: 0.5, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 12.5, italic: true, color: '9DAAC2', lineSpacingMultiple: 1.1,
  });

  s.addNotes([
    "[5:35-6:25] Anti-proxy. 50 seconds. Slow down here.",
    "",
    "This is the part I found most interesting, because the obvious implementation is worse than useless.",
    "",
    "Issue one QR code per session - what most tutorials show - and the first student in photographs it and sends it to the group chat. The whole class is marked present and the record looks legitimate. You haven't just failed to stop cheating; you've made it easier and given it an audit trail.",
    "",
    "So the code has to expire. Tokens live sixty seconds, and the projected page replaces them every forty-five. The strip along the bottom shows it: only the gold one works now.",
    "",
    "One trade-off, which I'll name rather than hide: a scan landing exactly on a rotation is rejected. Accepting the old token would weaken the guarantee permanently to save two seconds. So: scan again.",
  ].join('\n'));
}
