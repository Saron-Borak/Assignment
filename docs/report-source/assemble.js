const H = require('./report.js');
const {
  Document, Packer, Paragraph, TextRun, HeadingLevel, AlignmentType,
  LevelFormat, Header, Footer, PageNumber, BorderStyle, fs, NAVY, GREY,
} = H;

const { body: front } = require('./build.js');

// Code() and Figure() each return an array of paragraphs, so the assembled
// list is nested. docx requires a flat children array - an un-flattened entry
// serialises as an invalid element and corrupts the document.
const content = [
  ...front,
  ...require('./s1_intro.js'),
  ...require('./s2_srs.js'),
  ...require('./s3_design.js'),
  ...require('./s3c_erd.js').before,
  ...require('./s3b_database.js').tableDesigns,
  ...require('./s3d_norm.js'),
  ...require('./s4_impl.js'),
  ...require('./s5_testing.js'),
  ...require('./s6_walkthrough.js'),
  ...require('./s7_challenges.js'),
  ...require('./s8_future.js'),
  ...require('./s10_appendix.js'),
].flat(Infinity);

const doc = new Document({
  creator: 'EAMU Student Attendance Management System',
  title: 'Student Attendance Management System - Final Project Report',
  description: 'Final project report for the individual assignment.',
  styles: {
    default: {
      document: {
        run: { font: 'Calibri', size: 22, color: '1A1A1A' },
        paragraph: { spacing: { line: 288, after: 140 } },
      },
      heading1: {
        run: { font: 'Calibri Light', size: 34, bold: true, color: NAVY },
        paragraph: { spacing: { before: 320, after: 200 }, outlineLevel: 0 },
      },
      heading2: {
        run: { font: 'Calibri Light', size: 27, bold: true, color: NAVY },
        paragraph: { spacing: { before: 300, after: 140 }, outlineLevel: 1 },
      },
      heading3: {
        run: { font: 'Calibri', size: 23, bold: true, color: '2A3D5C' },
        paragraph: { spacing: { before: 240, after: 120 }, outlineLevel: 2 },
      },
    },
  },
  numbering: {
    config: [
      {
        reference: 'bullets',
        levels: [
          { level: 0, format: LevelFormat.BULLET, text: '\u2022', alignment: AlignmentType.LEFT,
            style: { paragraph: { indent: { left: 460, hanging: 260 } } } },
          { level: 1, format: LevelFormat.BULLET, text: '\u25E6', alignment: AlignmentType.LEFT,
            style: { paragraph: { indent: { left: 900, hanging: 260 } } } },
        ],
      },
      {
        reference: 'steps',
        levels: [
          { level: 0, format: LevelFormat.DECIMAL, text: '%1.', alignment: AlignmentType.START,
            style: { paragraph: { indent: { left: 460, hanging: 300 } } } },
        ],
      },
    ],
  },
  sections: [{
    properties: {
      page: {
        margin: { top: 1440, right: 1080, bottom: 1440, left: 1080 },
      },
    },
    headers: {
      default: new Header({
        children: [new Paragraph({
          alignment: AlignmentType.RIGHT,
          border: { bottom: { style: BorderStyle.SINGLE, size: 4, color: 'D6DBE4' } },
          spacing: { after: 200 },
          children: [new TextRun({
            text: 'Student Attendance Management System  |  East Asia Management University',
            size: 16, color: GREY,
          })],
        })],
      }),
    },
    footers: {
      default: new Footer({
        children: [new Paragraph({
          alignment: AlignmentType.CENTER,
          children: [new TextRun({ children: ['Page ', PageNumber.CURRENT, ' of ', PageNumber.TOTAL_PAGES], size: 16, color: GREY })],
        })],
      }),
    },
    children: content,
  }],
});

Packer.toBuffer(doc).then((buffer) => {
  const out = 'C:/Users/Rakze/Desktop/Assignment/docs/EAMU-Attendance-System-Final-Report.docx';
  fs.writeFileSync(out, buffer);
  console.log('written:', out);
  console.log('elements:', content.length);
  console.log('figures placeholders:', H.figureIndex().length);
  console.log('size KB:', Math.round(buffer.length / 1024));
});
