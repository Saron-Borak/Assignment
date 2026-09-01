<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\MarkedVia;
use App\Enums\SessionStatus;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SeedsAttendance;
use Tests\TestCase;

class AttendanceMarkingTest extends TestCase
{
    use RefreshDatabase, SeedsAttendance;

    public function test_a_lecturer_saves_one_record_per_student(): void
    {
        $lecturer = $this->makeLecturer();
        $section = $this->makeSection($lecturer);
        $session = AttendanceSession::factory()->create(['class_section_id' => $section->id]);

        $a = $this->makeStudent();
        $b = $this->makeStudent();
        $this->enroll($a, $section);
        $this->enroll($b, $section);

        $this->actingAs($lecturer->user)
            ->put(route('lecturer.sessions.mark.store', $session), [
                'marks' => [
                    $a->id => AttendanceStatus::Present->value,
                    $b->id => AttendanceStatus::Absent->value,
                ],
                'remarks' => [$b->id => 'Called in sick'],
            ])
            ->assertRedirect(route('lecturer.sessions.show', $session));

        $this->assertDatabaseCount('attendance_records', 2);
        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $session->id,
            'student_id' => $a->id,
            'status' => 'present',
            'marked_via' => 'manual',
        ]);
        $this->assertDatabaseHas('attendance_records', [
            'student_id' => $b->id,
            'status' => 'absent',
            'remarks' => 'Called in sick',
        ]);
    }

    public function test_re_saving_the_register_updates_rather_than_duplicates(): void
    {
        $lecturer = $this->makeLecturer();
        $section = $this->makeSection($lecturer);
        $session = AttendanceSession::factory()->create(['class_section_id' => $section->id]);
        $student = $this->makeStudent();
        $this->enroll($student, $section);

        $this->actingAs($lecturer->user)
            ->put(route('lecturer.sessions.mark.store', $session), ['marks' => [$student->id => 'absent']]);
        $this->actingAs($lecturer->user)
            ->put(route('lecturer.sessions.mark.store', $session), ['marks' => [$student->id => 'present']]);

        $this->assertDatabaseCount('attendance_records', 1);
        $this->assertDatabaseHas('attendance_records', [
            'student_id' => $student->id,
            'status' => 'present',
        ]);
    }

    public function test_marks_for_students_outside_the_roster_are_discarded(): void
    {
        $lecturer = $this->makeLecturer();
        $section = $this->makeSection($lecturer);
        $session = AttendanceSession::factory()->create(['class_section_id' => $section->id]);

        $enrolled = $this->makeStudent();
        $outsider = $this->makeStudent();
        $this->enroll($enrolled, $section);

        $this->actingAs($lecturer->user)
            ->put(route('lecturer.sessions.mark.store', $session), [
                'marks' => [
                    $enrolled->id => 'present',
                    $outsider->id => 'present',
                ],
            ]);

        $this->assertDatabaseCount('attendance_records', 1);
        $this->assertDatabaseMissing('attendance_records', ['student_id' => $outsider->id]);
    }

    public function test_closing_a_session_marks_everyone_unmarked_as_absent(): void
    {
        $lecturer = $this->makeLecturer();
        $section = $this->makeSection($lecturer);
        $session = AttendanceSession::factory()->open()->create(['class_section_id' => $section->id]);

        $checkedIn = $this->makeStudent();
        $noShowA = $this->makeStudent();
        $noShowB = $this->makeStudent();

        foreach ([$checkedIn, $noShowA, $noShowB] as $student) {
            $this->enroll($student, $section);
        }

        AttendanceRecord::factory()->create([
            'attendance_session_id' => $session->id,
            'student_id' => $checkedIn->id,
            'status' => AttendanceStatus::Late,
        ]);

        $this->actingAs($lecturer->user)
            ->put(route('lecturer.sessions.close', $session))
            ->assertRedirect(route('lecturer.sessions.show', $session));

        $this->assertDatabaseCount('attendance_records', 3);

        // The mark that already existed must survive untouched.
        $this->assertDatabaseHas('attendance_records', [
            'student_id' => $checkedIn->id,
            'status' => 'late',
        ]);

        foreach ([$noShowA, $noShowB] as $student) {
            $this->assertDatabaseHas('attendance_records', [
                'student_id' => $student->id,
                'status' => AttendanceStatus::Absent->value,
                'marked_via' => MarkedVia::System->value,
            ]);
        }

        $session->refresh();
        $this->assertSame(SessionStatus::Closed, $session->status);
        $this->assertNotNull($session->closed_at);
    }

    public function test_opening_a_session_issues_a_token_and_a_code(): void
    {
        $lecturer = $this->makeLecturer();
        $section = $this->makeSection($lecturer);
        $session = AttendanceSession::factory()->create(['class_section_id' => $section->id]);
        $this->enroll($this->makeStudent(), $section);

        $this->actingAs($lecturer->user)
            ->put(route('lecturer.sessions.open', $session))
            ->assertRedirect(route('lecturer.sessions.qr', $session));

        $session->refresh();
        $this->assertSame(SessionStatus::Open, $session->status);
        $this->assertSame(64, strlen($session->qr_token));
        $this->assertSame(6, strlen($session->checkin_code));
        $this->assertTrue($session->qr_expires_at->isFuture());
    }

    public function test_closing_a_session_retires_its_check_in_credentials(): void
    {
        $lecturer = $this->makeLecturer();
        $section = $this->makeSection($lecturer);
        $session = AttendanceSession::factory()->create(['class_section_id' => $section->id]);
        $this->enroll($this->makeStudent(), $section);

        $this->actingAs($lecturer->user)->put(route('lecturer.sessions.open', $session));
        $this->assertNotNull($session->refresh()->qr_token);

        $this->actingAs($lecturer->user)->put(route('lecturer.sessions.close', $session));

        $session->refresh();
        $this->assertNull($session->qr_token);
        $this->assertNull($session->checkin_code);
    }

    public function test_a_session_with_an_empty_roster_cannot_be_opened(): void
    {
        $lecturer = $this->makeLecturer();
        $session = AttendanceSession::factory()->create([
            'class_section_id' => $this->makeSection($lecturer)->id,
        ]);

        $this->actingAs($lecturer->user)
            ->put(route('lecturer.sessions.open', $session))
            ->assertSessionHas('error');

        $this->assertSame(SessionStatus::Scheduled, $session->refresh()->status);
    }
}
