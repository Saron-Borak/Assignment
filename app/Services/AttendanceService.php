<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\MarkedVia;
use App\Enums\SessionStatus;
use App\Exceptions\CheckInException;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\ClassSection;
use App\Models\Student;
use App\Models\User;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Every write to the attendance tables goes through this class, so the manual
 * register, the QR kiosk and the typed-code fallback can never drift apart.
 */
class AttendanceService
{
    /**
     * Open a session for check-in and mint its first QR token.
     */
    public function openSession(AttendanceSession $session, ?User $by = null): AttendanceSession
    {
        $session->forceFill([
            'status' => SessionStatus::Open,
            'opened_at' => $session->opened_at ?? now(),
            'closed_at' => null,
            'created_by' => $session->created_by ?? $by?->id,
        ])->save();

        return $this->rotateQr($session);
    }

    /**
     * Issue a fresh token and human-readable code. Rotating on every poll means
     * a screenshot of the projected code stops working within a minute.
     */
    public function rotateQr(AttendanceSession $session): AttendanceSession
    {
        $session->forceFill([
            'qr_token' => Str::random(64),
            'checkin_code' => $this->generateCheckInCode(),
            'qr_expires_at' => now()->addSeconds((int) config('attendance.qr_ttl_seconds')),
        ])->save();

        return $session;
    }

    /**
     * Close the register. Anyone still unmarked was not in the room, so they are
     * recorded absent in a single statement rather than one query per student.
     *
     * @return int Number of students auto-marked absent.
     */
    public function closeSession(AttendanceSession $session, ?User $by = null): int
    {
        return DB::transaction(function () use ($session, $by) {
            $alreadyMarked = $session->records()->pluck('student_id')->all();

            $unmarked = $session->classSection
                ->enrollments()
                ->active()
                ->whereNotIn('student_id', $alreadyMarked)
                ->pluck('student_id');

            if ($unmarked->isNotEmpty()) {
                $now = now();

                AttendanceRecord::insert($unmarked->map(fn (int $studentId) => [
                    'attendance_session_id' => $session->id,
                    'student_id' => $studentId,
                    'status' => AttendanceStatus::Absent->value,
                    'marked_via' => MarkedVia::System->value,
                    'marked_at' => $now,
                    'marked_by' => $by?->id,
                    'remarks' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all());
            }

            $session->forceFill([
                'status' => SessionStatus::Closed,
                'closed_at' => now(),
                // Retiring the credentials stops check-ins after the register closes.
                'qr_token' => null,
                'qr_expires_at' => null,
                'checkin_code' => null,
            ])->save();

            return $unmarked->count();
        });
    }

    /**
     * Reopen a closed session so a lecturer can correct a mistake.
     */
    public function reopenSession(AttendanceSession $session, ?User $by = null): AttendanceSession
    {
        return $this->openSession($session, $by);
    }

    /**
     * Persist the lecturer's manual register.
     *
     * @param  array<int, string>  $marks  student id => status value
     * @param  array<int, string|null>  $remarks  student id => note
     * @return int Number of records written.
     */
    public function saveMarks(AttendanceSession $session, array $marks, User $by, array $remarks = []): int
    {
        // Only students actually on the roster may be marked, so a tampered form
        // cannot create records against someone else's student id.
        $eligible = $session->classSection
            ->enrollments()
            ->active()
            ->pluck('student_id')
            ->flip();

        $now = now();
        $rows = [];

        foreach ($marks as $studentId => $status) {
            $studentId = (int) $studentId;

            if (! $eligible->has($studentId)) {
                continue;
            }

            $rows[] = [
                'attendance_session_id' => $session->id,
                'student_id' => $studentId,
                'status' => AttendanceStatus::from($status)->value,
                'marked_via' => MarkedVia::Manual->value,
                'marked_at' => $now,
                'marked_by' => $by->id,
                'remarks' => $remarks[$studentId] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows === []) {
            return 0;
        }

        DB::transaction(fn () => AttendanceRecord::upsert(
            $rows,
            ['attendance_session_id', 'student_id'],
            ['status', 'marked_via', 'marked_at', 'marked_by', 'remarks', 'updated_at'],
        ));

        return count($rows);
    }

    /**
     * Record a student's self check-in.
     *
     * @throws CheckInException
     */
    public function checkIn(Student $student, AttendanceSession $session, MarkedVia $via): AttendanceRecord
    {
        if (! $session->isOpen()) {
            throw CheckInException::sessionNotOpen();
        }

        if (! $student->isEnrolledIn($session->classSection)) {
            throw CheckInException::notEnrolled();
        }

        $existing = $session->records()->where('student_id', $student->id)->first();

        if ($existing) {
            throw CheckInException::alreadyCheckedIn($existing->status->label());
        }

        return AttendanceRecord::create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => $session->statusForCheckInAt(),
            'marked_via' => $via,
            'marked_at' => now(),
            'marked_by' => $student->user_id,
        ]);
    }

    /**
     * Find the open session a scanned token belongs to.
     *
     * @throws CheckInException
     */
    public function resolveByToken(string $token): AttendanceSession
    {
        $session = AttendanceSession::with('classSection.course')
            ->where('qr_token', $token)
            ->first();

        if (! $session) {
            throw CheckInException::invalidToken();
        }

        if (! $session->qrIsValid()) {
            throw CheckInException::expired();
        }

        return $session;
    }

    /**
     * Find the open session a typed six-character code belongs to.
     *
     * @throws CheckInException
     */
    public function resolveByCode(string $code): AttendanceSession
    {
        $session = AttendanceSession::with('classSection.course')
            ->where('checkin_code', strtoupper(trim($code)))
            ->where('status', SessionStatus::Open)
            ->first();

        if (! $session) {
            throw CheckInException::invalidToken();
        }

        if (! $session->qrIsValid()) {
            throw CheckInException::expired();
        }

        return $session;
    }

    /**
     * Create one scheduled session per timetabled slot between two dates,
     * skipping slots that already exist.
     *
     * @return int Number of sessions created.
     */
    public function generateSessions(
        ClassSection $section,
        CarbonInterface $from,
        CarbonInterface $to,
        ?User $by = null,
    ): int {
        $schedules = $section->schedules()->get();

        if ($schedules->isEmpty()) {
            return 0;
        }

        $existing = $section->sessions()
            ->whereBetween('session_date', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(fn (AttendanceSession $s) => $s->session_date->toDateString().' '.substr((string) $s->start_time, 0, 8))
            ->flip();

        $now = now();
        $rows = [];

        foreach (CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay()) as $day) {
            foreach ($schedules as $schedule) {
                if ($day->dayOfWeekIso !== $schedule->day_of_week) {
                    continue;
                }

                $start = substr((string) $schedule->start_time, 0, 8);

                if ($existing->has($day->toDateString().' '.$start)) {
                    continue;
                }

                $rows[] = [
                    'class_section_id' => $section->id,
                    'session_date' => $day->toDateString(),
                    'start_time' => $start,
                    'end_time' => substr((string) $schedule->end_time, 0, 8),
                    'topic' => null,
                    'status' => SessionStatus::Scheduled->value,
                    'late_after_minutes' => (int) config('attendance.late_after_minutes'),
                    'created_by' => $by?->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows === []) {
            return 0;
        }

        DB::transaction(fn () => AttendanceSession::insert($rows));

        return count($rows);
    }

    /**
     * Six characters from an alphabet with no 0/O or 1/I, so the projected code
     * cannot be misread from the back of a lecture hall.
     */
    protected function generateCheckInCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $max = strlen($alphabet) - 1;

        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $alphabet[random_int(0, $max)];
            }
        } while (AttendanceSession::where('checkin_code', $code)->exists());

        return $code;
    }
}
