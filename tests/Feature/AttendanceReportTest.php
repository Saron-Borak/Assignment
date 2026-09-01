<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\ClassSection;
use App\Models\Student;
use App\Services\AttendanceReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SeedsAttendance;
use Tests\TestCase;

class AttendanceReportTest extends TestCase
{
    use RefreshDatabase, SeedsAttendance;

    protected AttendanceReportService $reports;

    protected ClassSection $section;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reports = app(AttendanceReportService::class);
        $this->section = $this->makeSection();
    }

    /**
     * Create $count closed sessions on consecutive past days.
     *
     * @return \Illuminate\Support\Collection<int, AttendanceSession>
     */
    protected function closedSessions(int $count): \Illuminate\Support\Collection
    {
        return collect(range(1, $count))->map(fn (int $i) => AttendanceSession::factory()->closed()->create([
            'class_section_id' => $this->section->id,
            'session_date' => now()->subDays($i)->toDateString(),
            'start_time' => sprintf('%02d:00:00', 7 + $i),
            'end_time' => sprintf('%02d:00:00', 9 + $i),
        ]));
    }

    /** @param  array<int, string>  $statuses */
    protected function markAcross(\Illuminate\Support\Collection $sessions, Student $student, array $statuses): void
    {
        foreach ($statuses as $i => $status) {
            AttendanceRecord::factory()->create([
                'attendance_session_id' => $sessions[$i]->id,
                'student_id' => $student->id,
                'status' => AttendanceStatus::from($status),
            ]);
        }
    }

    public function test_a_perfect_record_is_one_hundred_percent(): void
    {
        $student = $this->makeStudent();
        $this->enroll($student, $this->section);
        $sessions = $this->closedSessions(4);
        $this->markAcross($sessions, $student, ['present', 'present', 'present', 'present']);

        $stats = $this->reports->studentClassStats($student, $this->section);

        $this->assertSame(4, $stats['held']);
        $this->assertSame(4, $stats['countable']);
        $this->assertSame(100.0, $stats['percentage']);
        $this->assertFalse($stats['at_risk']);
    }

    public function test_absences_reduce_the_percentage(): void
    {
        $student = $this->makeStudent();
        $this->enroll($student, $this->section);
        $sessions = $this->closedSessions(4);
        $this->markAcross($sessions, $student, ['present', 'present', 'absent', 'absent']);

        $stats = $this->reports->studentClassStats($student, $this->section);

        $this->assertSame(50.0, $stats['percentage']);
        $this->assertTrue($stats['at_risk']);
    }

    /**
     * A late arrival still means the student was in the room, so the default
     * policy counts it towards attendance.
     */
    public function test_late_arrivals_count_as_attended_under_the_default_policy(): void
    {
        config(['attendance.count_late_as_present' => true]);

        $student = $this->makeStudent();
        $this->enroll($student, $this->section);
        $sessions = $this->closedSessions(4);
        $this->markAcross($sessions, $student, ['present', 'late', 'late', 'absent']);

        $stats = $this->reports->studentClassStats($student, $this->section);

        $this->assertSame(3, $stats['attended']);
        $this->assertSame(1, $stats['present']);
        $this->assertSame(2, $stats['late']);
        $this->assertSame(75.0, $stats['percentage']);
    }

    public function test_late_arrivals_can_be_excluded_by_policy(): void
    {
        config(['attendance.count_late_as_present' => false]);

        $student = $this->makeStudent();
        $this->enroll($student, $this->section);
        $sessions = $this->closedSessions(4);
        $this->markAcross($sessions, $student, ['present', 'late', 'late', 'absent']);

        $stats = $this->reports->studentClassStats($student, $this->section);

        $this->assertSame(1, $stats['attended']);
        $this->assertSame(25.0, $stats['percentage']);
    }

    /**
     * An approved absence should not be held against the student, so it leaves
     * the denominator entirely.
     */
    public function test_an_excused_absence_is_removed_from_the_denominator(): void
    {
        $student = $this->makeStudent();
        $this->enroll($student, $this->section);
        $sessions = $this->closedSessions(4);
        $this->markAcross($sessions, $student, ['present', 'present', 'excused', 'absent']);

        $stats = $this->reports->studentClassStats($student, $this->section);

        $this->assertSame(4, $stats['held']);
        $this->assertSame(3, $stats['countable']);
        $this->assertSame(1, $stats['excused']);
        $this->assertSame(66.7, $stats['percentage']);
    }

    public function test_open_and_scheduled_sessions_are_excluded(): void
    {
        $student = $this->makeStudent();
        $this->enroll($student, $this->section);
        $sessions = $this->closedSessions(2);
        $this->markAcross($sessions, $student, ['present', 'present']);

        AttendanceSession::factory()->open()->create([
            'class_section_id' => $this->section->id,
            'session_date' => now()->toDateString(),
            'start_time' => '13:00:00', 'end_time' => '15:00:00',
        ]);
        AttendanceSession::factory()->create([
            'class_section_id' => $this->section->id,
            'session_date' => now()->addDay()->toDateString(),
            'start_time' => '13:00:00', 'end_time' => '15:00:00',
        ]);

        $stats = $this->reports->studentClassStats($student, $this->section);

        // Only the two closed sessions count; the upcoming ones must not drag
        // the figure down.
        $this->assertSame(2, $stats['held']);
        $this->assertSame(100.0, $stats['percentage']);
    }

    public function test_a_student_with_no_sessions_yet_reports_zeroes_and_is_not_at_risk(): void
    {
        $student = $this->makeStudent();
        $this->enroll($student, $this->section);

        $stats = $this->reports->studentClassStats($student, $this->section);

        $this->assertSame(0, $stats['held']);
        $this->assertSame(0.0, $stats['percentage']);
        $this->assertFalse($stats['at_risk']);
    }

    public function test_the_at_risk_list_only_contains_students_below_the_threshold(): void
    {
        $good = $this->makeStudent();
        $poor = $this->makeStudent();
        $this->enroll($good, $this->section);
        $this->enroll($poor, $this->section);

        $sessions = $this->closedSessions(4);
        $this->markAcross($sessions, $good, ['present', 'present', 'present', 'present']);
        $this->markAcross($sessions, $poor, ['present', 'absent', 'absent', 'absent']);

        $rows = $this->reports->lowAttendance();

        $this->assertCount(1, $rows);
        $this->assertSame($poor->id, $rows->first()->student_id);
        $this->assertSame(25.0, $rows->first()->percentage);
    }

    public function test_the_at_risk_list_is_ordered_worst_first(): void
    {
        $sessions = $this->closedSessions(4);

        foreach (['present,absent,absent,absent', 'present,present,absent,absent'] as $plan) {
            $student = $this->makeStudent();
            $this->enroll($student, $this->section);
            $this->markAcross($sessions, $student, explode(',', $plan));
        }

        $rows = $this->reports->lowAttendance();

        $this->assertCount(2, $rows);
        $this->assertSame(25.0, $rows[0]->percentage);
        $this->assertSame(50.0, $rows[1]->percentage);
    }

    /**
     * The reports must stay flat as the roster grows - a per-student query here
     * would make every report page scale with enrollment.
     */
    public function test_a_whole_roster_is_summarised_in_a_single_query(): void
    {
        $sessions = $this->closedSessions(3);

        foreach (range(1, 10) as $i) {
            $student = $this->makeStudent();
            $this->enroll($student, $this->section);
            $this->markAcross($sessions, $student, ['present', 'late', 'absent']);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $stats = $this->reports->classSectionStats($this->section);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(10, $stats);
        $this->assertCount(1, $queries, 'The roster summary should cost exactly one query.');
    }

    public function test_the_university_total_aggregates_every_section(): void
    {
        $student = $this->makeStudent();
        $this->enroll($student, $this->section);
        $sessions = $this->closedSessions(4);
        $this->markAcross($sessions, $student, ['present', 'present', 'present', 'absent']);

        $totals = $this->reports->universityTotals();

        $this->assertSame(4, $totals['held']);
        $this->assertSame(3, $totals['attended']);
        $this->assertSame(75.0, $totals['percentage']);
    }
}
