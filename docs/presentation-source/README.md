# Presentation source

The deck is generated rather than hand-drawn, so it can be rebuilt after a change
to the system.

```bash
npm install pptxgenjs
node build_deck.js
```

Output: `../EAMU-Attendance-System-Presentation.pptx`

## Speaker notes

Notes live in `notes_a.py`, `notes_b.py` and `notes_c.py`, budgeted at roughly
145 words per minute. After editing them:

```bash
python apply_notes.py    # writes them back into the slide modules
node build_deck.js
```

## Quality checks

No LibreOffice is installed on this machine, so the deck cannot be rendered to
images. These two scripts check the defects a render would have caught:

```bash
python qa_layout.py      # off-slide shapes, tight margins, overlapping text
python qa_overflow.py    # estimated text overflow, by simulating word wrap
```

Both must report clean. Run the schema validator as well:

```bash
python <pptx-skill>/scripts/office/validate.py ../EAMU-Attendance-System-Presentation.pptx
```
