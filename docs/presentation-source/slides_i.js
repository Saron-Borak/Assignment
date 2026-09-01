const D = require('./deck.js');
const {
  pres, newSlide, heading, statusDots, card, stat,
  NAVY, NAVY_2, GOLD, INK, MUTED, LINE, TINT, WHITE,
  GREEN, AMBER, RED, SLATE, HEAD, BODY, W, Hh, M, CW,
} = D;

// ===========================================================================
// 17 - CONCLUSION  (dark)
// ===========================================================================
{
  const s = newSlide(true);

  s.addText('IN CLOSING', {
    x: M, y: 1.16, w: CW, h: 0.32, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 12, bold: true, charSpacing: 2.4, color: GOLD,
  });
  s.addText('Late, untrusted data made a real rule unenforceable.', {
    x: M, y: 1.6, w: 11.4, h: 0.6, isTextBox: true, margin: 0,
    fontFace: HEAD, fontSize: 29, bold: true, color: WHITE,
  });
  s.addText('The system fixes both halves: the percentage is current the moment a register closes, and the record is far harder to falsify.', {
    x: M, y: 2.24, w: 11.4, h: 0.52, isTextBox: true, margin: 0,
    fontFace: BODY, fontSize: 14.5, color: 'B9C6DB', lineSpacingMultiple: 1.14,
  });

  const takeaways = [
    ['Constraints improved the design', 'No image extension forced SVG, which beat the PNG I had planned. Every limitation made the result better.'],
    ['One service beat three copies', 'A late change to the closing rule was one edit, not three - and none could be forgotten.'],
    ['The silent bugs mattered most', 'All three defects returned no error. They were found by using the system, not by testing it.'],
  ];
  const cw = (CW - 0.64) / 3;
  takeaways.forEach((t, i) => {
    const x = M + i * (cw + 0.32);
    s.addShape(pres.ShapeType.roundRect, {
      x, y: 3.06, w: cw, h: 1.86, rectRadius: 0.05,
      fill: { color: NAVY_2 }, line: { color: '2C4574', width: 1 },
    });
    s.addText(t[0], {
      x: x + 0.28, y: 3.32, w: cw - 0.56, h: 0.58, isTextBox: true, margin: 0,
      fontFace: HEAD, fontSize: 15, bold: true, color: WHITE, lineSpacingMultiple: 1.0,
    });
    s.addText(t[1], {
      x: x + 0.28, y: 3.94, w: cw - 0.56, h: 0.84, isTextBox: true, margin: 0,
      fontFace: BODY, fontSize: 11.5, color: '9DAAC2', lineSpacingMultiple: 1.1,
    });
  });

  const figs = [['39', 'requirements delivered'], ['73', 'tests passing'], ['3', 'defects found and fixed'], ['0', 'build steps required']];
  const fw = (CW - 0.96) / 4;
  figs.forEach((f, i) => {
    const x = M + i * (fw + 0.32);
    s.addText(f[0], {
      x, y: 5.16, w: fw, h: 0.58, isTextBox: true, margin: 0,
      fontFace: HEAD, fontSize: 34, bold: true, color: GOLD,
    });
    s.addText(f[1], {
      x, y: 5.76, w: fw, h: 0.36, isTextBox: true, margin: 0,
      fontFace: BODY, fontSize: 11, color: '8E9EB8',
    });
  });

  statusDots(s, M, 6.42, 0.16, true);
  s.addText('Thank you  -  questions welcome', {
    x: M + 1.26, y: 6.34, w: 6.0, h: 0.34, isTextBox: true, margin: 0,
    fontFace: HEAD, fontSize: 15, bold: true, color: WHITE,
  });

  s.addNotes([
    "[13:25-14:00] Close. 35 seconds, then stop talking.",
    "",
    "To close where I started. The university had a real rule and no usable data to enforce it. The system fixes both halves - the percentage is current from the moment a register closes, and the record is much harder to falsify than a sheet of paper.",
    "",
    "Three things I take away. Constraints improved the design rather than limiting it. One shared service beat three copies of the same rules. And the defects that mattered were silent, which is why I verified by using the system and not only by testing it.",
    "",
    "Thank you - I'm happy to take questions.",
    "",
    "--------------------------------------------------",
    "LIKELY QUESTIONS AND SHORT ANSWERS",
    "",
    "Q: Can a student still cheat by sending the live code to a friend?",
    "A: Yes, within the 45-second window, and I wouldn't claim otherwise. That's exactly the gap location-verified check-in closes - requiring the request to come from the campus network. It's on the future scope slide for that reason.",
    "",
    "Q: Why no framework starter kit for authentication?",
    "A: I wanted every step explicit and defensible. I still used the framework for hashing, CSRF and parameter binding, because hand-rolling those is where vulnerabilities come from.",
    "",
    "Q: What if the internet drops - Bootstrap is on a CDN?",
    "A: The page still works, it just loses styling. The alternative was a build step, which I traded away deliberately so it runs anywhere without setup.",
    "",
    "Q: Why derive the percentage instead of caching it?",
    "A: A cached value drifts the moment any write path forgets to update it, and a wrong percentage here can bar a student from an exam. Deriving it costs one query, which I measured and test for.",
    "",
    "Q: How would this scale to a whole university?",
    "A: Reporting is already flat - one query regardless of roster size. The next bottleneck would be the kiosk polling, which I'd move to a push connection rather than a 45-second poll.",
    "",
    "Q: What was the hardest part?",
    "A: Realising the obvious QR implementation was worse than useless - it makes cheating easier and gives it an audit trail. Getting from there to rotating tokens, and being honest about the trade-off, was the most interesting problem in the project.",
  ].join('\n'));
}
