<?php

namespace Tests\Feature;

use App\Models\AttendanceSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SeedsAttendance;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase, SeedsAttendance;

    public function test_a_student_cannot_reach_the_admin_area(): void
    {
        $this->actingAs($this->makeStudent()->user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_a_student_cannot_reach_the_lecturer_area(): void
    {
        $this->actingAs($this->makeStudent()->user)
            ->get(route('lecturer.dashboard'))
            ->assertForbidden();
    }

    public function test_a_lecturer_cannot_reach_the_admin_area(): void
    {
        $this->actingAs($this->makeLecturer()->user)
            ->get(route('admin.students.index'))
            ->assertForbidden();
    }

    public function test_an_administrator_cannot_reach_the_student_area(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('student.dashboard'))
            ->assertForbidden();
    }

    /**
     * Route middleware alone would let one lecturer edit another's register by
     * guessing an id, so the policy has to stop it.
     */
    public function test_a_lecturer_cannot_open_a_session_belonging_to_another_lecturer(): void
    {
        $owner = $this->makeLecturer();
        $intruder = $this->makeLecturer();

        $session = AttendanceSession::factory()->create([
            'class_section_id' => $this->makeSection($owner)->id,
        ]);

        $this->actingAs($intruder->user)
            ->put(route('lecturer.sessions.open', $session))
            ->assertForbidden();

        $this->assertDatabaseHas('attendance_sessions', [
            'id' => $session->id,
            'status' => 'scheduled',
        ]);
    }

    public function test_a_lecturer_cannot_mark_a_register_belonging_to_another_lecturer(): void
    {
        $owner = $this->makeLecturer();
        $intruder = $this->makeLecturer();

        $session = AttendanceSession::factory()->create([
            'class_section_id' => $this->makeSection($owner)->id,
        ]);

        $this->actingAs($intruder->user)
            ->get(route('lecturer.sessions.mark', $session))
            ->assertForbidden();
    }

    public function test_a_lecturer_cannot_view_a_class_they_do_not_teach(): void
    {
        $section = $this->makeSection($this->makeLecturer());

        $this->actingAs($this->makeLecturer()->user)
            ->get(route('lecturer.classes.show', $section))
            ->assertForbidden();
    }

    public function test_a_student_cannot_view_attendance_for_a_class_they_are_not_enrolled_in(): void
    {
        $section = $this->makeSection();

        $this->actingAs($this->makeStudent()->user)
            ->get(route('student.attendance.show', $section))
            ->assertForbidden();
    }

    public function test_a_student_can_view_attendance_for_their_own_class(): void
    {
        $student = $this->makeStudent();
        $section = $this->makeSection();
        $this->enroll($student, $section);

        $this->actingAs($student->user)
            ->get(route('student.attendance.show', $section))
            ->assertOk();
    }
}
