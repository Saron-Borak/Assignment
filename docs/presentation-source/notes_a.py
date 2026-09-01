# Tightened speaker scripts, budgeted at ~145 words per minute.
# Key: (source file, index of the addNotes call within that file)
NOTES = {}

NOTES[('slides_a.js', 0)] = """[0:00-0:25] Opening.

Good morning. My project is a Student Attendance Management System for East Asia Management University.

In one line: it records attendance at the moment a class happens, and makes each student's percentage visible while there is still time to act on it.

I'll spend about seven minutes on the problem and the design, three minutes demonstrating it live, and the rest on testing and what I learned."""

NOTES[('slides_b.js', 0)] = """[0:25-1:25] The problem. 60 seconds.

The university already records attendance on paper, and already enforces a 75% rule. So the problem isn't that attendance is unmeasured. It's that the measurement is useless by the time anyone sees it.

Four failures. First and most important: totals are added up at the end of term, so a student failing the requirement finds out when they're barred from the exam, not while they can still fix it.

Second, roll-call costs minutes out of every session, all semester.

Third, a sheet passed around a room is trivially signed by a friend, and a lost sheet loses the session outright.

Fourth, three parties hold three versions, with no way to reconcile them.

So the rule is real, but the data behind it is late and not trusted."""

NOTES[('slides_b.js', 1)] = """[1:25-2:05] Objectives. 40 seconds.

Everything here serves one rule, on the left: 75% attendance in every class, and falling below it can bar you from the exam.

From that, six objectives. Model the university properly. Separate the three roles. Make marking fast enough that a lecturer will actually use it. Remove roll-call altogether with self check-in - and make that resistant to cheating, which was the hardest part. Calculate continuously rather than at the end. And prove it works with tests.

All six were delivered. I'll come back to the two that were genuinely difficult: the check-in, and the arithmetic."""

NOTES[('slides_c.js', 0)] = """[2:05-2:45] The three portals. 40 seconds.

One application, three portals, and the separation is strict.

The administrator is the registry: academic structure, accounts, rosters, every report.

The lecturer sees only their own classes. They generate a term of sessions from the timetable in one action, mark the register, project the QR code, close the session.

The student sees only their own record - their percentage, a warning below 75%, and their history.

The line at the bottom matters. Access is checked twice. Middleware asks "is this the right kind of user for this area". A policy asks "is this the right individual for this record". Without the second check, one lecturer could edit another's register just by changing the number in the URL."""

NOTES[('slides_c.js', 1)] = """[2:45-3:20] Technology. 35 seconds.

Laravel 13 on PHP 8.5, with MariaDB through XAMPP. I leaned on the framework for the security primitives specifically - hashing, CSRF, parameter binding - because hand-rolling those is where student projects get vulnerabilities.

Two things on the right. There's no build step: Bootstrap loads from a CDN, so no Node, no npm, no compile. It runs anywhere after composer install and a migration.

And below it - this machine has neither PHP image extension, which rules out generating the QR as a PNG. So I chose a library that emits SVG in pure PHP, and vector is actually sharper on a projector. The constraint improved the result."""

NOTES[('slides_d.js', 0)] = """[3:20-4:05] Database design. 45 seconds.

Twelve tables in third normal form. The shape to notice: users, in navy, is the single authentication table. Students and lecturers are profile tables hanging off it. Administrators have no profile row, because they have no academic attributes.

Bottom left is the most important constraint in the schema: unique on session plus student. One outcome per student per session, enforced by the database rather than by application code. That's what makes check-in safe - scan twice and the second insert simply cannot happen.

Bottom right is a decision I'll defend: I don't cache the percentage anywhere. It would make reporting trivial, but it would drift the moment any write path forgot to update it. So it's always derived."""

NOTES[('slides_d.js', 1)] = """[4:05-4:50] Architecture. 45 seconds.

This is the design decision I'm most confident about.

Attendance can be written three ways: a lecturer marking a register, a student scanning a code, or a student typing one. Three entry points, one destination. All three go through a single service. No controller contains an attendance rule.

The reason is on the right. Three copies of the logic would eventually disagree - about what counts as late, or who may be marked - and you'd get contradictory records with no way to tell which is right.

And it wasn't theoretical. Mid-project the closing rule changed: closing a register should also retire the check-in code. One service meant one edit, and all three paths inherited it. With three copies I'd have missed one."""
