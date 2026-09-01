const D = require('./deck.js');

// Order matters: each module adds its slides to the shared presentation.
require('./slides_a.js');   // 1  title
require('./slides_b.js');   // 2  problem          3  objectives
require('./slides_c.js');   // 4  portals          5  technology
require('./slides_d.js');   // 6  database         7  architecture
require('./slides_e.js');   // 8  qr flow          9  anti-proxy
require('./slides_f.js');   // 10 percentage       11 reporting
require('./slides_g.js');   // 12 demo             13 testing
require('./slides_h.js');   // 14 defects  15 challenges  16 future
require('./slides_i.js');   // 17 conclusion

const out = 'C:/Users/Rakze/Desktop/Assignment/docs/EAMU-Attendance-System-Presentation.pptx';
D.pres.writeFile({ fileName: out }).then(() => {
  console.log('written:', out);
  console.log('slides:', D.slides());
});
