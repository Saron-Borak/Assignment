<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Enums\MarkedVia;
use App\Enums\SessionStatus;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\ClassSection;
use App\Models\Semester;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * A term's worth of attendance history so reports are meaningful on first run.
 *
 * Sessions before today are closed and fully marked. Today's and future sessions
 * are left scheduled, so a lecturer can immediately demonstrate opening a class
 * and taking a register.
 */
class AttendanceHistorySeeder extends Seeder
{
    /** Roughly one student in eight is seeded below the attendance threshold. */
    protected const AT_RISK_EVERY = 8;

    public function __construct(protected AttendanceService $attendance) {}

    public function run(): void
    {
        // Deterministic history: re-seeding reproduces the same figures.
        mt_srand(20260827);

        $semester = Semester::where('is_active', true)->firstOrFail();
        $admin = User::where('email', 'admin@eamu.edu')->first();
        $today = Carbon::today();

        $sections = ClassSection::with(['schedules', 'lecturer'])
            ->where('semester_id', $semester->id)
            ->get();

        foreach ($sections as $section) {
            $this->attendance->generateSessions(
                $section,
                $semester->start_date,
                $semester->end_date,
                $admin,
            );
        }

        $this->recordPastSessions($semester, $today);
    }

    protected function recordPastSessions(Semester $semester, Carbon $today): void
    {
        $sections = ClassSection::with('lecturer.user')
            ->where('semester_id', $semester->id)
            ->get();

        foreach ($sections as $section) {
            $roster = $section->enrollments()->active()->pluck('student_id');

            if ($roster->isEmpty()) {
                continue;
            }

            $sessions = $section->sessions()
                ->whereDate('session_date', '<', $today)
                ->where('status', SessionStatus::Scheduled)
                ->orderBy('session_date')
                ->get();

            if ($sessions->isEmpty()) {
                continue;
            }

            $markedBy = $section->lecturer->user_id;
            $rows = [];

            foreach ($sessions as $session) {
                $start = $session->startsAt();

                foreach ($roster as $studentId) {
                    $status = $this->rollStatus($studentId);

                    $rows[] = [
                        'attendance_session_id' => $session->id,
                        'student_id' => $studentId,
                        'status' => $status->value,
                        'marked_via' => $this->rollMarkedVia($status),
                        'marked_at' => $status === AttendanceStatus::Late
                            ? $start->copy()->addMinutes(mt_rand(16, 40))
                            : $start->copy()->addMinutes(mt_rand(-6, 12)),
                        'marked_by' => $markedBy,
                        'remarks' => $status === AttendanceStatus::Excused ? 'Approved absence' : null,
                        'created_at' => $start,
                        'updated_at' => $start,
                    ];
                }
            }

            // Chunked so a large history does not exceed the placeholder limit.
            foreach (array_chunk($rows, 500) as $chunk) {
                AttendanceRecord::upsert(
                    $chunk,
                    ['attendance_session_id', 'student_id'],
                    ['status', 'marked_via', 'marked_at', 'marked_by', 'remarks', 'updated_at'],
                );
            }

            AttendanceSession::whereIn('id', $sessions->pluck('id'))->update([
                'status' => SessionStatus::Closed,
                'opened_at' => now(),
                'closed_at' => now(),
            ]);
        }
    }

    /**
     * Weighted outcome. Students on the at-risk list miss far more classes so
     * the low-attendance report has something real to show.
     */
    protected function rollStatus(int $studentId): AttendanceStatus
    {
        $roll = mt_rand(1, 100);

        if ($studentId % self::AT_RISK_EVERY === 0) {
            return match (true) {
                $roll <= 55 => AttendanceStatus::Present,
                $roll <= 62 => AttendanceStatus::Late,
                $roll <= 97 => AttendanceStatus::Absent,
                default => AttendanceStatus::Excused,
            };
        }

        return match (true) {
            $roll <= 85 => AttendanceStatus::Present,
            $roll <= 92 => AttendanceStatus::Late,
            $roll <= 98 => AttendanceStatus::Absent,
            default => AttendanceStatus::Excused,
        };
    }

    /**
     * Absences are only ever produced by the lecturer or by closing the
     * register; students self check-in when they are actually there.
     */
    protected function rollMarkedVia(AttendanceStatus $status): string
    {
        if ($status === AttendanceStatus::Absent) {
            return MarkedVia::System->value;
        }

        if ($status === AttendanceStatus::Excused) {
            return MarkedVia::Manual->value;
        }

        return match (mt_rand(1, 3)) {
            1 => MarkedVia::Manual->value,
            2 => MarkedVia::Qr->value,
            default => MarkedVia::Code->value,
        };
    }
}
