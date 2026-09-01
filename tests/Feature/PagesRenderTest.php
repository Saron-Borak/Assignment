<?php

namespace Tests\Feature;

use App\Models\AttendanceSession;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SeedsAttendance;
use Tests\TestCase;

/**
 * Walks every screen with real data. This is what catches a typo in a Blade
 * template or a relation that was never eager-loaded, since the test suite runs
 * with lazy loading disabled.
 */
class PagesRenderTest extends TestCase
{
    use RefreshDatabase, SeedsAttendance;

    public function test_every_admin_screen_renders(): void
    {
        $admin = User::factory()->admin()->create();
        $lecturer = $this->makeLecturer();
        $section = $this->makeSection($lecturer);
        $student = $this->makeStudent();
        $this->enroll($student, $section);

        AttendanceSession::factory()->closed()->create(['class_section_id' => $section->id]);

        $routes = [
            route('admin.dashboard'),
            route('admin.faculties.index'),
            route('admin.faculties.create'),
            route('admin.faculties.edit', $this->makeFaculty()),
            route('admin.programs.index'),
            route('admin.programs.create'),
            route('admin.programs.edit', $student->program),
            route('admin.courses.index'),
            route('admin.courses.create'),
            route('admin.courses.edit', $section->course),
            route('admin.semesters.index'),
            route('admin.semesters.create'),
            route('admin.semesters.edit', $this->makeSemester()),
            route('admin.lecturers.index'),
            route('admin.lecturers.create'),
            route('admin.lecturers.show', $lecturer),
            route('admin.lecturers.edit', $lecturer),
            route('admin.students.index'),
            route('admin.students.create'),
            route('admin.students.show', $student),
            route('admin.students.edit', $student),
            route('admin.class-sections.index'),
            route('admin.class-sections.create'),
            route('admin.class-sections.show', $section),
            route('admin.class-sections.edit', $section),
            route('admin.class-sections.enrollments.edit', $section),
            route('admin.users.index'),
            route('admin.reports.index'),
            route('admin.reports.low-attendance'),
            route('admin.reports.class-section', $section),
            route('admin.reports.student', $student),
            route('profile.edit'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($admin)->get($url)->assertOk("Failed rendering {$url}");
        }
    }

    public function test_every_lecturer_screen_renders(): void
    {
        $lecturer = $this->makeLecturer();
        $section = $this->makeSection($lecturer);
        $this->enroll($this->makeStudent(), $section);

        $session = AttendanceSession::factory()->closed()->create(['class_section_id' => $section->id]);

        $routes = [
            route('lecturer.dashboard'),
            route('lecturer.classes.index'),
            route('lecturer.classes.show', $section),
            route('lecturer.classes.report', $section),
            route('lecturer.sessions.index'),
            route('lecturer.sessions.create', $section),
            route('lecturer.sessions.show', $session),
            route('lecturer.sessions.mark', $session),
            route('profile.edit'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($lecturer->user)->get($url)->assertOk("Failed rendering {$url}");
        }
    }

    public function test_the_qr_kiosk_renders_a_scannable_code(): void
    {
        $lecturer = $this->makeLecturer();
        $section = $this->makeSection($lecturer);
        $this->enroll($this->makeStudent(), $section);

        $session = AttendanceSession::factory()->create(['class_section_id' => $section->id]);
        app(AttendanceService::class)->openSession($session, $lecturer->user);

        $this->actingAs($lecturer->user)
            ->get(route('lecturer.sessions.qr', $session))
            ->assertOk()
            // An inline SVG, since neither ext-gd nor ext-imagick is assumed.
            ->assertSee('<svg', false)
            ->assertSee($session->refresh()->checkin_code);
    }

    public function test_the_kiosk_redirects_when_the_session_is_not_open(): void
    {
        $lecturer = $this->makeLecturer();
        $session = AttendanceSession::factory()->create([
            'class_section_id' => $this->makeSection($lecturer)->id,
        ]);

        $this->actingAs($lecturer->user)
            ->get(route('lecturer.sessions.qr', $session))
            ->assertRedirect(route('lecturer.sessions.show', $session));
    }

    public function test_every_student_screen_renders(): void
    {
        $student = $this->makeStudent();
        $section = $this->makeSection();
        $this->enroll($student, $section);

        AttendanceSession::factory()->closed()->create(['class_section_id' => $section->id]);

        $routes = [
            route('student.dashboard'),
            route('student.attendance.index'),
            route('student.attendance.show', $section),
            route('student.check-in.create'),
            route('profile.edit'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($student->user)->get($url)->assertOk("Failed rendering {$url}");
        }
    }

    public function test_the_root_url_sends_each_role_to_their_own_portal(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/')->assertRedirect(route('admin.dashboard'));

        $this->actingAs($this->makeLecturer()->user)
            ->get('/')->assertRedirect(route('lecturer.dashboard'));

        $this->actingAs($this->makeStudent()->user)
            ->get('/')->assertRedirect(route('student.dashboard'));
    }
}
