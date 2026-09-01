const {
  Document, Packer, Paragraph, TextRun, HeadingLevel, AlignmentType,
  Table, TableRow, TableCell, WidthType, ShadingType, BorderStyle,
  PageBreak, TableOfContents, LevelFormat, Header, Footer, PageNumber,
  convertInchesToTwip,
} = require('docx');
const fs = require('fs');

const NAVY = '12233F';
const GOLD = '8A6D0B';
const GREY = '5A6472';

// ---------- text helpers ----------
const P = (text, opts = {}) => new Paragraph({
  spacing: { after: opts.after ?? 140, line: 288 },
  alignment: opts.align,
  children: [new TextRun({ text, italics: opts.italics, bold: opts.bold, color: opts.color, size: opts.size })],
});

const Rich = (runs, opts = {}) => new Paragraph({
  spacing: { after: opts.after ?? 140, line: 288 },
  children: runs.map(r => typeof r === 'string'
    ? new TextRun({ text: r })
    : new TextRun(r)),
});

const H1 = (text) => new Paragraph({
  text, heading: HeadingLevel.HEADING_1, pageBreakBefore: true,
  spacing: { before: 240, after: 200 },
});
const H2 = (text) => new Paragraph({ text, heading: HeadingLevel.HEADING_2, spacing: { before: 280, after: 140 } });
const H3 = (text) => new Paragraph({ text, heading: HeadingLevel.HEADING_3, spacing: { before: 220, after: 120 } });

const Bullet = (text, level = 0) => new Paragraph({
  text, numbering: { reference: 'bullets', level },
  spacing: { after: 70, line: 288 },
});

const Num = (text, level = 0) => new Paragraph({
  text, numbering: { reference: 'steps', level },
  spacing: { after: 70, line: 288 },
});

// ---------- figure placeholder ----------
// Figures 1-4 are numbered inline in sections 3, 4 and 5; the walkthrough
// placeholders continue from there.
let figureCount = 4;
const figureIndex = [];
const box = (color) => ({
  top: { style: BorderStyle.SINGLE, size: 6, color },
  bottom: { style: BorderStyle.SINGLE, size: 6, color },
  left: { style: BorderStyle.SINGLE, size: 6, color },
  right: { style: BorderStyle.SINGLE, size: 6, color },
});

const Figure = (caption, instruction) => {
  figureCount += 1;
  figureIndex.push({ n: figureCount, caption });
  return [
    new Paragraph({
      shading: { type: ShadingType.CLEAR, color: 'auto', fill: 'FFF6DA' },
      border: box('E0C060'),
      spacing: { before: 200, after: 60 },
      children: [
        new TextRun({ text: 'INSERT SCREENSHOT  —  ', bold: true, color: GOLD, size: 19 }),
        new TextRun({ text: instruction, color: '6B5A20', size: 19 }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { after: 240 },
      children: [new TextRun({ text: `Figure ${figureCount}: ${caption}`, italics: true, size: 18, color: GREY })],
    }),
  ];
};

// ---------- code block ----------
const Code = (lines) => lines.map((line, i) => new Paragraph({
  shading: { type: ShadingType.CLEAR, color: 'auto', fill: 'F4F6FA' },
  spacing: { before: i === 0 ? 120 : 0, after: i === lines.length - 1 ? 180 : 0, line: 240 },
  children: [new TextRun({ text: line || ' ', font: 'Consolas', size: 17 })],
}));

// ---------- table ----------
const PAGE_W = 9360; // usable width in DXA for A4 with 1" margins

const cell = (content, { w, bold, fill, align, size } = {}) => new TableCell({
  width: { size: w, type: WidthType.DXA },
  shading: fill ? { type: ShadingType.CLEAR, color: 'auto', fill } : undefined,
  margins: { top: 60, bottom: 60, left: 100, right: 100 },
  children: (Array.isArray(content) ? content : [content]).map(t => new Paragraph({
    alignment: align,
    spacing: { after: 0, line: 252 },
    children: [new TextRun({ text: String(t), bold, size: size ?? 18 })],
  })),
});

const Tbl = (headings, rows, widths) => {
  const cols = widths || headings.map(() => Math.floor(PAGE_W / headings.length));
  return new Table({
    width: { size: PAGE_W, type: WidthType.DXA },
    columnWidths: cols,
    rows: [
      new TableRow({
        tableHeader: true,
        children: headings.map((h, i) => cell(h, { w: cols[i], bold: true, fill: 'E8ECF3' })),
      }),
      ...rows.map(r => new TableRow({
        children: r.map((c, i) => cell(c, { w: cols[i] })),
      })),
    ],
  });
};

const TableCaption = (() => {
  let n = 0;
  return (text) => {
    n += 1;
    return new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 60, after: 220 },
      children: [new TextRun({ text: `Table ${n}: ${text}`, italics: true, size: 18, color: GREY })],
    });
  };
})();

module.exports = {
  Document, Packer, Paragraph, TextRun, HeadingLevel, AlignmentType,
  Table, TableRow, TableCell, WidthType, ShadingType, BorderStyle,
  PageBreak, TableOfContents, LevelFormat, Header, Footer, PageNumber,
  fs, NAVY, GOLD, GREY, P, Rich, H1, H2, H3, Bullet, Num,
  Figure, Code, Tbl, TableCaption, PAGE_W, box,
  figureIndex: () => figureIndex,
};
