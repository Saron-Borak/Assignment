<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Support\SeedsAttendance;
use Tests\TestCase;

class CsvExportTest extends TestCase
{
    use RefreshDatabase, SeedsAttendance;

    /**
     * Drain a streamed download into a string so its contents can be asserted.
     */
    protected function csvBody(TestResponse $response): string
    {
        ob_start();
        $response->sendContent();

        return ob_get_clean();
    }

    public function test_an_administrator_downloads_a_class_register(): void
    {
        $section = $this->makeSection();
        $student = $this->makeStudent();
        $this->enroll($student, $section);

        $session = AttendanceSession::factory()->closed()->create(['class_section_id' => $section->id]);
        AttendanceRecord::factory()->create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => AttendanceStatus::Present,
        ]);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.reports.class-section.export', $section))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertDownload();

        $body = $this->csvBody($response);

        // fputcsv quotes any heading containing a space.
        $this->assertStringContainsString('"Student No",Name,Email', $body);
        $this->assertStringContainsString($student->student_no, $body);
        $this->assertStringContainsString($student->user->name, $body);
        $this->assertStringContainsString('100.0', $body);
    }

    public function test_the_export_is_prefixed_with_a_utf8_byte_order_mark(): void
    {
        $section = $this->makeSection();
        $this->enroll($this->makeStudent(), $section);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.reports.class-section.export', $section));

        // Without the BOM, Excel misreads non-ASCII student names.
        $this->assertStringStartsWith("\xEF\xBB\xBF", $this->csvBody($response));
    }

    public function test_the_at_risk_export_only_lists_students_below_the_threshold(): void
    {
        $section = $this->makeSection();
        $good = $this->makeStudent();
        $poor = $this->makeStudent();
        $this->enroll($good, $section);
        $this->enroll($poor, $section);

        $sessions = collect(range(1, 4))->map(fn ($i) => AttendanceSession::factory()->closed()->create([
            'class_section_id' => $section->id,
            'session_date' => now()->subDays($i)->toDateString(),
            'start_time' => sprintf('%02d:00:00', 7 + $i),
            'end_time' => sprintf('%02d:00:00', 9 + $i),
        ]));

        foreach ($sessions as $i => $session) {
            AttendanceRecord::factory()->create([
                'attendance_session_id' => $session->id,
                'student_id' => $good->id,
                'status' => AttendanceStatus::Present,
            ]);
            AttendanceRecord::factory()->create([
                'attendance_session_id' => $session->id,
                'student_id' => $poor->id,
                'status' => $i === 0 ? AttendanceStatus::Present : AttendanceStatus::Absent,
            ]);
        }

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.reports.low-attendance.export'))
            ->assertOk();

        $body = $this->csvBody($response);

        $this->assertStringContainsString($poor->student_no, $body);
        $this->assertStringNotContainsString($good->student_no, $body);
    }

    public function test_a_lecturer_exports_only_their_own_class(): void
    {
        $owner = $this->makeLecturer();
        $section = $this->makeSection($owner);
        $this->enroll($this->makeStudent(), $section);

        $this->actingAs($owner->user)
            ->get(route('lecturer.classes.report.export', $section))
            ->assertOk();

        $this->actingAs($this->makeLecturer()->user)
            ->get(route('lecturer.classes.report.export', $section))
            ->assertForbidden();
    }

    public function test_a_student_cannot_download_any_report(): void
    {
        $section = $this->makeSection();
        $student = $this->makeStudent();
        $this->enroll($student, $section);

        $this->actingAs($student->user)
            ->get(route('admin.reports.class-section.export', $section))
            ->assertForbidden();

        $this->actingAs($student->user)
            ->get(route('lecturer.classes.report.export', $section))
            ->assertForbidden();
    }

    public function test_a_student_record_exports_one_row_per_class(): void
    {
        $student = $this->makeStudent();
        $first = $this->makeSection();
        $second = $this->makeSection();
        $this->enroll($student, $first);
        $this->enroll($student, $second);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.reports.student.export', $student))
            ->assertOk();

        $body = $this->csvBody($response);

        $this->assertStringContainsString($first->course->code, $body);
        $this->assertStringContainsString($second->course->code, $body);
    }
}
