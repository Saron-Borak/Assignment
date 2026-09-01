<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\MarkedVia;
use App\Models\AttendanceSession;
use App\Models\ClassSection;
use App\Models\Lecturer;
use App\Models\Student;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SeedsAttendance;
use Tests\TestCase;

class QrCheckInTest extends TestCase
{
    use RefreshDatabase, SeedsAttendance;

    protected Lecturer $lecturer;

    protected ClassSection $section;

    protected AttendanceSession $session;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lecturer = $this->makeLecturer();
        $this->section = $this->makeSection($this->lecturer);
        $this->student = $this->makeStudent();
        $this->enroll($this->student, $this->section);

        // A session that started a few minutes ago, so a check-in right now is
        // comfortably inside the on-time window.
        $this->session = AttendanceSession::factory()->create([
            'class_section_id' => $this->section->id,
            'session_date' => now()->toDateString(),
            'start_time' => now()->subMinutes(5)->format('H:i:s'),
            'end_time' => now()->addHours(2)->format('H:i:s'),
        ]);

        app(AttendanceService::class)->openSession($this->session, $this->lecturer->user);
        $this->session->refresh();
    }

    public function test_scanning_a_valid_code_marks_the_student_present(): void
    {
        $this->actingAs($this->student->user)
            ->get(route('checkin.token', $this->session->qr_token))
            ->assertRedirect(route('student.dashboard'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $this->session->id,
            'student_id' => $this->student->id,
            'status' => AttendanceStatus::Present->value,
            'marked_via' => MarkedVia::Qr->value,
        ]);
    }

    public function test_the_typed_code_records_the_same_outcome(): void
    {
        $this->actingAs($this->student->user)
            ->post(route('student.check-in.store'), ['code' => $this->session->checkin_code])
            ->assertRedirect(route('student.dashboard'));

        $this->assertDatabaseHas('attendance_records', [
            'student_id' => $this->student->id,
            'status' => AttendanceStatus::Present->value,
            'marked_via' => MarkedVia::Code->value,
        ]);
    }

    public function test_the_typed_code_is_case_insensitive(): void
    {
        $this->actingAs($this->student->user)
            ->post(route('student.check-in.store'), ['code' => strtolower($this->session->checkin_code)])
            ->assertSessionHas('success');

        $this->assertDatabaseCount('attendance_records', 1);
    }

    public function test_an_expired_token_is_rejected(): void
    {
        $this->session->forceFill(['qr_expires_at' => now()->subMinute()])->save();

        $this->actingAs($this->student->user)
            ->get(route('checkin.token', $this->session->qr_token))
            ->assertRedirect(route('student.check-in.create'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_an_unknown_token_is_rejected(): void
    {
        $this->actingAs($this->student->user)
            ->get(route('checkin.token', str_repeat('z', 64)))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('attendance_records', 0);
    }

    /**
     * Rotation is what stops a screenshot of the projected code being shared,
     * so the superseded token must stop working immediately.
     */
    public function test_a_rotated_token_stops_working(): void
    {
        $stale = $this->session->qr_token;

        app(AttendanceService::class)->rotateQr($this->session);

        $this->actingAs($this->student->user)
            ->get(route('checkin.token', $stale))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_a_student_who_is_not_enrolled_cannot_check_in(): void
    {
        $outsider = $this->makeStudent();

        $this->actingAs($outsider->user)
            ->get(route('checkin.token', $this->session->qr_token))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_a_student_cannot_check_in_twice(): void
    {
        $this->actingAs($this->student->user)->get(route('checkin.token', $this->session->qr_token));
        $this->assertDatabaseCount('attendance_records', 1);

        $this->actingAs($this->student->user)
            ->get(route('checkin.token', $this->session->qr_token))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('attendance_records', 1);
    }

    public function test_a_closed_session_cannot_be_checked_into(): void
    {
        $token = $this->session->qr_token;

        app(AttendanceService::class)->closeSession($this->session, $this->lecturer->user);

        $this->actingAs($this->student->user)
            ->get(route('checkin.token', $token))
            ->assertSessionHas('error');

        // Closing marked the student absent; no check-in record was added.
        $this->assertDatabaseHas('attendance_records', [
            'student_id' => $this->student->id,
            'status' => AttendanceStatus::Absent->value,
        ]);
    }

    public function test_a_guest_scanning_the_code_is_sent_to_login_and_returned_afterwards(): void
    {
        $this->get(route('checkin.token', $this->session->qr_token))
            ->assertRedirect(route('login'));

        $this->post('/login', [
            'email' => $this->student->user->email,
            'password' => 'password',
        ])->assertRedirect(route('checkin.token', $this->session->qr_token));
    }

    public function test_checking_in_after_the_grace_period_is_recorded_as_late(): void
    {
        $grace = (int) config('attendance.late_after_minutes');

        // Push the start time back so "now" falls past the late threshold.
        $this->session->forceFill([
            'start_time' => now()->subMinutes($grace + 10)->format('H:i:s'),
        ])->save();

        $this->actingAs($this->student->user)
            ->get(route('checkin.token', $this->session->refresh()->qr_token))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_records', [
            'student_id' => $this->student->id,
            'status' => AttendanceStatus::Late->value,
            'marked_via' => MarkedVia::Qr->value,
        ]);
    }

    public function test_the_kiosk_refresh_endpoint_rotates_the_token_and_reports_the_roster(): void
    {
        $before = $this->session->qr_token;

        $this->actingAs($this->student->user)->get(route('checkin.token', $before));

        $response = $this->actingAs($this->lecturer->user)
            ->getJson(route('lecturer.sessions.qr.refresh', $this->session))
            ->assertOk()
            ->assertJsonStructure(['qr_svg', 'code', 'expires_in', 'present', 'late', 'checked_in', 'total', 'recent']);

        $this->assertSame(1, $response->json('present'));
        $this->assertSame(1, $response->json('total'));
        $this->assertNotSame($before, $this->session->refresh()->qr_token);
    }

    public function test_the_kiosk_tells_the_page_when_the_session_has_been_closed(): void
    {
        app(AttendanceService::class)->closeSession($this->session, $this->lecturer->user);

        $this->actingAs($this->lecturer->user)
            ->getJson(route('lecturer.sessions.qr.refresh', $this->session))
            ->assertOk()
            ->assertJson(['closed' => true]);
    }

    public function test_another_lecturer_cannot_poll_the_kiosk_endpoint(): void
    {
        $this->actingAs($this->makeLecturer()->user)
            ->getJson(route('lecturer.sessions.qr.refresh', $this->session))
            ->assertForbidden();
    }
}
