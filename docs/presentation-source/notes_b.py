NOTES = {}

NOTES[('slides_e.js', 0)] = """[4:50-5:35] The check-in flow. 45 seconds.

Here's the whole flow - I'll demonstrate it shortly.

The lecturer opens the session. The system issues two credentials: a long random token inside the QR code, and a six-character code a human can read. That gets projected. Students scan it with the phone camera - no app to install, it's just a URL.

Step four is where the real work is. Four conditions must all hold: the token is current, the session is open, the student is actually enrolled, and they haven't already checked in. Any one fails and it's refused with a message written for the student.

Step five decides present or late. Closing the register marks everyone else absent.

Note the typed-code fallback - that's how I'll demo this without passing my phone around."""

NOTES[('slides_e.js', 1)] = """[5:35-6:25] Anti-proxy. 50 seconds. Slow down here.

This is the part I found most interesting, because the obvious implementation is worse than useless.

Issue one QR code per session - what most tutorials show - and the first student in photographs it and sends it to the group chat. The whole class is marked present and the record looks legitimate. You haven't just failed to stop cheating; you've made it easier and given it an audit trail.

So the code has to expire. Tokens live sixty seconds, and the projected page replaces them every forty-five. The strip along the bottom shows it: only the gold one works now.

One trade-off, which I'll name rather than hide: a scan landing exactly on a rotation is rejected. Accepting the old token would weaken the guarantee permanently to save two seconds. So: scan again."""

NOTES[('slides_f.js', 0)] = """[6:25-7:10] The percentage rule. 45 seconds.

This is the calculation the whole system exists to produce.

Attended is present plus late, because a late student was still in the room. That's configurable if the university disagrees.

Countable is the interesting half: closed sessions minus excused absences.

Three decisions there. First, only closed sessions count - a session open right now, or scheduled for next week, is excluded. That's what lets a lecturer open a register without everyone briefly showing as absent.

Second, an excused absence leaves the denominator entirely. It's not a forgiven miss; it's a session that never counted.

Third, the 75% is configuration, not code.

And the edge case at the bottom: a class that hasn't met returns zero and is explicitly not at risk - otherwise everyone would look like they were failing on day one."""

NOTES[('slides_f.js', 1)] = """[7:10-7:45] Reporting performance. 35 seconds.

One more design point, then the demo.

The natural way to build this report is to loop the students and calculate each one. Thirty students, thirty queries. University-wide, hundreds. It grows with enrollment - so it works in a demo with five students and falls over with a real cohort.

Instead every report is a single grouped query using conditional sums. SQL produces all the counters in one pass; PHP only does the final division. Thirty students and three hundred cost the same.

And there's a test that counts the queries and fails if it's more than one. So this can't quietly regress - reintroduce a loop and the build breaks."""

NOTES[('slides_g.js', 1)] = """[10:45-11:25] Testing. 40 seconds.

Seventy-three tests, two hundred and forty-nine assertions, and the suite runs in under three seconds - which matters, because a slow suite is one you stop running.

The important choice is on the right: tests go through the actual routes, not straight to method calls. So one assertion exercises routing, middleware, the ownership policies, validation, the service layer and the schema together.

The other setting worth mentioning is lazy loading, which I have disabled in tests. If the code forgets to eager-load a relationship it throws instead of quietly firing an extra query per row - so an N+1 performance bug fails the build rather than shipping silently.

The report documents fifty test cases individually, each traced to a numbered requirement."""

NOTES[('slides_h.js', 0)] = """[11:25-12:20] Defects. 55 seconds. This slide earns credibility - don't rush it.

I want to show you three defects rather than claim there were none, because how I found them is the point. All three were silent. Not one raised an error.

First: calling select after withCount threw away the counting subquery, so several screens showed blank cells. The page returned 200, the layout was perfect, one number was missing. No test that checks the page loads could catch that.

Second: a 500 from an ambiguous column name once a join was added. That screen had no test. It does now - and so does every other screen.

Third is my favourite. A check-in at half ten for an eight o'clock class was recorded present, not late - the app ran in UTC while the timetable stores local time. My tests could not have found this: they build times relative to now, so they stay self-consistent in any timezone and pass regardless.

The lesson: all three were found by using the system, not testing it. You need both."""

NOTES[('slides_h.js', 1)] = """[12:20-12:55] Challenges. 35 seconds. Move briskly.

Four genuine challenges, and a pattern across them.

No image extension, which pushed me to SVG - and SVG beat the PNG I originally wanted.

The static QR problem, which drove the rotation design and forced me to be explicit about the trade-off rather than pretend there wasn't one.

A migration that failed on an index name being too long, which taught me to test against the real database rather than assume a schema that reads correctly will apply.

And three write paths, which produced the service layer.

The pattern is the point: in every case the constraint made the design better than the version I had in mind before I hit it."""

NOTES[('slides_h.js', 2)] = """[12:55-13:25] Future scope. 30 seconds - keep it short.

Briefly, what comes next, ordered by value against effort.

The left column is nearly free, because the hard part already exists. Notification is the clearest example: the system already knows exactly who is at risk. It just doesn't tell them yet. That's the highest-value thing I'd add, because it closes the loop on the original problem - the point was to act early, and right now someone still has to remember to read the report.

The middle column is real but well-defined work. The right column needs hardware or a much bigger scope."""
