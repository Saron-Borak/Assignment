"""
Replace the addNotes(...) block in each slide module with the tightened script.

The notes are written as plain Python strings and re-emitted as a JS array of
lines, so the generator stays the single source of truth for the deck.
"""
import io, json, re, os

BS = chr(92)

os.chdir(os.path.dirname(os.path.abspath(__file__)))

import notes_a, notes_b, notes_c
NOTES = {}
for mod in (notes_a, notes_b, notes_c):
    NOTES.update(mod.NOTES)

BLOCK = re.compile(r"  s{0}.addNotes{0}({0}[.*?join{0}([^)]*{0}){0});".format(BS), re.S)

by_file = {}
for (fn, idx), text in NOTES.items():
    by_file.setdefault(fn, {})[idx] = text

for fn, entries in sorted(by_file.items()):
    src = io.open(fn, encoding='utf-8').read()
    blocks = list(BLOCK.finditer(src))
    if len(blocks) <= max(entries):
        raise SystemExit(f"{fn}: expected >{max(entries)} addNotes blocks, found {len(blocks)}")

    out, last = [], 0
    for i, m in enumerate(blocks):
        out.append(src[last:m.start()])
        if i in entries:
            lines = entries[i].split('\n')
            js = ",\n".join("    " + json.dumps(l) for l in lines)
            joiner = "  ].join('" + BS + "n'));"
            out.append("  s.addNotes([" + chr(10) + js + "," + chr(10) + joiner)
        else:
            out.append(m.group(0))
        last = m.end()
    out.append(src[last:])

    io.open(fn, 'w', encoding='utf-8', newline='\n').write("".join(out))
    print(f"  {fn}: replaced {len(entries)} of {len(blocks)} note block(s)")

print("done")
