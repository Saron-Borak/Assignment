const H = require('./report.js');
const {
  Document, Packer, Paragraph, TextRun, HeadingLevel, AlignmentType,
  TableOfContents, LevelFormat, Header, Footer, PageNumber, PageBreak,
  ShadingType, BorderStyle, fs, NAVY, GOLD, GREY,
  P, Rich, H1, H2, H3, Bullet, Num, Figure, Code, Tbl, TableCaption, box,
} = H;

const body = [];
const add = (...items) => items.flat().forEach(i => body.push(i));

// =====================================================================
// TITLE PAGE
// =====================================================================
const titleLine = (text, opts = {}) => new Paragraph({
  alignment: AlignmentType.CENTER,
  spacing: { after: opts.after ?? 120 },
  children: [new TextRun({
    text, bold: opts.bold, size: opts.size ?? 24,
    color: opts.color, allCaps: opts.caps, font: opts.font,
  })],
});

add(
  new Paragraph({ spacing: { after: 900 }, children: [] }),
  titleLine('EAST ASIA MANAGEMENT UNIVERSITY', { bold: true, size: 26, color: NAVY, after: 60 }),
  titleLine('Faculty of Computing and Information Technology', { size: 22, color: GREY, after: 900 }),
  new Paragraph({
    alignment: AlignmentType.CENTER,
    border: { top: { style: BorderStyle.SINGLE, size: 12, color: GOLD } },
    spacing: { after: 200 },
    children: [],
  }),
  titleLine('STUDENT ATTENDANCE', { bold: true, size: 48, color: NAVY, after: 40 }),
  titleLine('MANAGEMENT SYSTEM', { bold: true, size: 48, color: NAVY, after: 160 }),
  titleLine('A web-based attendance platform with QR self check-in', { size: 22, color: GREY, after: 60 }),
  titleLine('and automated attendance reporting', { size: 22, color: GREY, after: 200 }),
  new Paragraph({
    alignment: AlignmentType.CENTER,
    border: { bottom: { style: BorderStyle.SINGLE, size: 12, color: GOLD } },
    spacing: { after: 700 },
    children: [],
  }),
  titleLine('Final Project Report', { bold: true, size: 26, after: 500 }),
);

const info = [
  ['Student Name', '[ Your Full Name ]'],
  ['Student ID', '[ Your Student ID ]'],
  ['Course', '[ Course Code and Title ]'],
  ['Supervisor', '[ Lecturer Name ]'],
  ['Submission Date', '[ Date ]'],
];
add(Tbl(['Field', 'Details'], info, [2800, 4200]));

add(
  new Paragraph({ spacing: { before: 600 }, children: [] }),
  titleLine('Built with Laravel 13, PHP 8.5 and MySQL / MariaDB', { size: 19, color: GREY, italics: true }),
  new Paragraph({ children: [new PageBreak()] }),
);

// =====================================================================
// DECLARATION
// =====================================================================
add(
  titleLine('Declaration', { bold: true, size: 34, color: NAVY, after: 200 }),
  P('I declare that this report and the accompanying software are my own work, produced for the individual assignment described in the course brief. All external libraries and frameworks used are listed in Section 2.6 and referenced in full at the end of this report. Where the work of others has been consulted, it is acknowledged.'),
  new Paragraph({ spacing: { before: 700 }, children: [] }),
  P('Signed: ____________________________         Date: __________________'),
  new Paragraph({ children: [new PageBreak()] }),
);

// =====================================================================
// TABLE OF CONTENTS
// =====================================================================
add(
  titleLine('Table of Contents', { bold: true, size: 34, color: NAVY, after: 200 }),
  new TableOfContents('Contents', { hyperlink: true, headingStyleRange: '1-3' }),
  Rich([{ text: 'To populate this table in Microsoft Word: select it, then press F9 and choose "Update entire table".', italics: true, size: 18, color: GREY }], { after: 0 }),
  new Paragraph({ children: [new PageBreak()] }),
);

module.exports = { body, add, H };
