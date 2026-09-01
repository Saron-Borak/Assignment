const pptxgen = require('pptxgenjs');

// ---------------------------------------------------------------------------
// Palette: the university navy and gold, plus the attendance status colours the
// application itself uses. Reusing that status language as the deck accent
// system keeps the slides tied to the subject rather than generically branded.
// ---------------------------------------------------------------------------
const NAVY   = '12233F';
const NAVY_2 = '1B3157';
const GOLD   = 'C9A227';
const INK    = '1A2333';
const MUTED  = '6B7891';
const LINE   = 'D8DEE8';
const TINT   = 'F4F6FA';
const WHITE  = 'FFFFFF';

const GREEN = '2E7D52';   // present
const AMBER = 'C9820A';   // late
const RED   = 'C0392B';   // absent
const SLATE = '8C96A5';   // excused

const HEAD = 'Cambria';
const BODY = 'Calibri';

const W = 13.333, Hh = 7.5;
const M = 0.7;                 // page margin
const CW = W - M * 2;          // content width = 11.933

const pres = new pptxgen();
pres.layout = 'LAYOUT_WIDE';
pres.author = 'EAMU Student Attendance Management System';
pres.title = 'Student Attendance Management System';

let slideNo = 0;

// ---------------------------------------------------------------------------
// Slide scaffolding
// ---------------------------------------------------------------------------
function newSlide(dark) {
  const s = pres.addSlide();
  s.background = { color: dark ? NAVY : WHITE };
  slideNo += 1;
  if (slideNo > 1) {
    s.addText(String(slideNo), {
      x: W - 1.0, y: Hh - 0.52, w: 0.5, h: 0.3, isTextBox: true, margin: 0,
      align: 'right', fontFace: BODY, fontSize: 10,
      color: dark ? '5B6B85' : 'A9B2C1',
    });
  }
  return s;
}

// Section title with an optional lead-in line beneath.
function heading(s, text, sub, dark) {
  s.addText(text, {
    x: M, y: 0.46, w: CW, h: 0.62, isTextBox: true, margin: 0,
    fontFace: HEAD, fontSize: 32, bold: true,
    color: dark ? WHITE : NAVY,
  });
  if (sub) {
    s.addText(sub, {
      x: M, y: 1.10, w: CW, h: 0.34, isTextBox: true, margin: 0,
      fontFace: BODY, fontSize: 14, italic: true,
      color: dark ? 'A9B7CE' : MUTED,
    });
  }
}

// The recurring motif: four status dots in the application's own colours.
function statusDots(s, x, y, size, dark) {
  [GREEN, AMBER, RED, SLATE].forEach((c, i) => {
    s.addShape(pres.ShapeType.ellipse, {
      x: x + i * (size * 1.75), y, w: size, h: size,
      fill: { color: c },
      line: { color: dark ? NAVY : WHITE, width: 0 },
    });
  });
}

// A tinted content card. Body may be a string or an array of bullet lines.
function card(s, o) {
  s.addShape(pres.ShapeType.roundRect, {
    x: o.x, y: o.y, w: o.w, h: o.h,
    rectRadius: 0.06,
    fill: { color: o.fill || TINT },
    line: { color: o.border || LINE, width: 1 },
  });
  let ty = o.y + 0.22;
  if (o.kicker) {
    s.addText(o.kicker, {
      x: o.x + 0.26, y: ty, w: o.w - 0.52, h: 0.24, isTextBox: true, margin: 0,
      fontFace: BODY, fontSize: 10.5, bold: true, charSpacing: 1.2,
      color: o.kickerColor || GOLD,
    });
    ty += 0.28;
  }
  if (o.title) {
    s.addText(o.title, {
      x: o.x + 0.26, y: ty, w: o.w - 0.52, h: o.titleH || 0.32, isTextBox: true, margin: 0,
      fontFace: HEAD, fontSize: o.titleSize || 15, bold: true, color: o.titleColor || NAVY,
    });
    ty += (o.titleH || 0.32) + 0.06;
  }
  if (o.body) {
    const rows = Array.isArray(o.body) ? o.body : [o.body];
    s.addText(
      rows.map((t, i) => ({
        text: t,
        options: { bullet: rows.length > 1, breakLine: i < rows.length - 1 },
      })),
      {
        x: o.x + 0.26, y: ty, w: o.w - 0.52, h: o.y + o.h - ty - 0.18,
        isTextBox: true, margin: 0, valign: 'top',
        fontFace: BODY, fontSize: o.bodySize || 12.5, color: o.bodyColor || INK,
        lineSpacingMultiple: 1.08, paraSpaceAfter: rows.length > 1 ? 5 : 0,
      },
    );
  }
}

// Large figure with a caption underneath.
function stat(s, o) {
  s.addText(o.value, {
    x: o.x, y: o.y, w: o.w, h: o.h || 0.78, isTextBox: true, margin: 0,
    align: o.align || 'left', fontFace: HEAD, fontSize: o.size || 44, bold: true,
    color: o.color || NAVY,
  });
  s.addText(o.label, {
    x: o.x, y: o.y + (o.h || 0.78) - 0.02, w: o.w, h: 0.5, isTextBox: true, margin: 0,
    align: o.align || 'left', fontFace: BODY, fontSize: o.labelSize || 11.5,
    color: o.labelColor || MUTED, lineSpacingMultiple: 1.0,
  });
}

module.exports = {
  pres, newSlide, heading, statusDots, card, stat,
  NAVY, NAVY_2, GOLD, INK, MUTED, LINE, TINT, WHITE,
  GREEN, AMBER, RED, SLATE, HEAD, BODY, W, Hh, M, CW,
  slides: () => slideNo,
};
