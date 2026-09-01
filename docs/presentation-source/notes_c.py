NOTES = {}

NOTES[('slides_g.js', 0)] = """[7:45-10:45] LIVE DEMONSTRATION. Three minutes. Keep moving.

BEFORE YOU START - have all of this ready:
  - Server already running: php artisan serve
  - Two browsers (or one normal + one private window), so you can be lecturer and student at once
  - Browser 1 signed in as admin@eamu.edu / password
  - Browser 2 sitting on the login page
  - If the data has been clicked around: php artisan migrate:fresh --seed

RUNNING ORDER:

1. ADMIN (35s). Dashboard - 60 students, 10 sections, 91.1% overall. Then the at-risk report: seven students below 75%, worst first. Say: this is the number the paper system could not produce until it was too late.

2. LECTURER (40s). Sign in as c.nou@eamu.edu. Point out only two classes - the scoping is real. Open today's session. The projection screen appears: QR code, six-character code, countdown, zero of thirty checked in.

3. STUDENT (35s). Second browser, sign in as kosal.tep@student.eamu.edu. Note the green banner that detected the open session. Type the six-character code. Confirmation: checked in as present.

4. BACK TO LECTURER (35s). Refresh the projection - the counter has moved. Open the marking register: thirty students, and the one who checked in carries a "Code self check-in" badge.

5. CLOSE IT (20s). Twenty-nine marked absent automatically; the self check-in preserved as present.

6. ACCESS CONTROL (15s). As the student, paste an /admin URL. Access denied page.

IF THE DEMO FAILS: do not debug on stage. Say "I have screenshots of this in the report", move to the testing slide, and recover afterwards."""

NOTES[('slides_i.js', 0)] = """[13:25-14:00] Close. 35 seconds, then stop talking.

To close where I started. The university had a real rule and no usable data to enforce it. The system fixes both halves - the percentage is current from the moment a register closes, and the record is much harder to falsify than a sheet of paper.

Three things I take away. Constraints improved the design rather than limiting it. One shared service beat three copies of the same rules. And the defects that mattered were silent, which is why I verified by using the system and not only by testing it.

Thank you - I'm happy to take questions.

--------------------------------------------------
LIKELY QUESTIONS AND SHORT ANSWERS

Q: Can a student still cheat by sending the live code to a friend?
A: Yes, within the 45-second window, and I wouldn't claim otherwise. That's exactly the gap location-verified check-in closes - requiring the request to come from the campus network. It's on the future scope slide for that reason.

Q: Why no framework starter kit for authentication?
A: I wanted every step explicit and defensible. I still used the framework for hashing, CSRF and parameter binding, because hand-rolling those is where vulnerabilities come from.

Q: What if the internet drops - Bootstrap is on a CDN?
A: The page still works, it just loses styling. The alternative was a build step, which I traded away deliberately so it runs anywhere without setup.

Q: Why derive the percentage instead of caching it?
A: A cached value drifts the moment any write path forgets to update it, and a wrong percentage here can bar a student from an exam. Deriving it costs one query, which I measured and test for.

Q: How would this scale to a whole university?
A: Reporting is already flat - one query regardless of roster size. The next bottleneck would be the kiosk polling, which I'd move to a push connection rather than a 45-second poll.

Q: What was the hardest part?
A: Realising the obvious QR implementation was worse than useless - it makes cheating easier and gives it an audit trail. Getting from there to rotating tokens, and being honest about the trade-off, was the most interesting problem in the project."""
