<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Support\SeedsAttendance;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase, SeedsAttendance;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_an_administrator_creates_a_course(): void
    {
        $faculty = $this->makeFaculty();

        $this->actingAs($this->admin)
            ->post(route('admin.courses.store'), [
                'faculty_id' => $faculty->id,
                'code' => 'cs210',
                'title' => 'Database Systems',
                'credit_hours' => 3,
            ])
            ->assertRedirect(route('admin.courses.index'));

        // The code is normalised to upper case before it is stored.
        $this->assertDatabaseHas('courses', ['code' => 'CS210', 'title' => 'Database Systems']);
    }

    public function test_a_duplicate_course_code_is_rejected(): void
    {
        $faculty = $this->makeFaculty();
        Course::factory()->create(['faculty_id' => $faculty->id, 'code' => 'CS210']);

        $this->actingAs($this->admin)
            ->post(route('admin.courses.store'), [
                'faculty_id' => $faculty->id,
                'code' => 'CS210',
                'title' => 'Another Course',
                'credit_hours' => 3,
            ])
            ->assertSessionHasErrors('code');
    }

    public function test_creating_a_lecturer_also_creates_their_sign_in_account(): void
    {
        $faculty = $this->makeFaculty();

        $this->actingAs($this->admin)
            ->post(route('admin.lecturers.store'), [
                'name' => 'Vannak Meas',
                'email' => 'v.meas@eamu.edu',
                'password' => 'secret-password',
                'password_confirmation' => 'secret-password',
                'faculty_id' => $faculty->id,
                'staff_no' => 'EAMU-L-0001',
                'title' => 'Dr.',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.lecturers.index'));

        $this->assertDatabaseHas('users', ['email' => 'v.meas@eamu.edu', 'role' => 'lecturer']);
        $this->assertDatabaseHas('lecturers', ['staff_no' => 'EAMU-L-0001']);
    }

    public function test_creating_a_student_also_creates_their_sign_in_account(): void
    {
        $program = Program::factory()->create(['faculty_id' => $this->makeFaculty()->id]);

        $this->actingAs($this->admin)
            ->post(route('admin.students.store'), [
                'name' => 'Sophea Chea',
                'email' => 'sophea.chea@student.eamu.edu',
                'password' => 'secret-password',
                'password_confirmation' => 'secret-password',
                'program_id' => $program->id,
                'student_no' => 'EAMU-2026-0001',
                'intake_year' => 2026,
                'status' => 'active',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('users', ['email' => 'sophea.chea@student.eamu.edu', 'role' => 'student']);
        $this->assertDatabaseHas('students', ['student_no' => 'EAMU-2026-0001']);
    }

    public function test_a_class_section_is_created_with_its_timetable(): void
    {
        $course = Course::factory()->create(['faculty_id' => $this->makeFaculty()->id]);

        $this->actingAs($this->admin)
            ->post(route('admin.class-sections.store'), [
                'course_id' => $course->id,
                'semester_id' => $this->makeSemester()->id,
                'lecturer_id' => $this->makeLecturer()->id,
                'section_code' => 'a',
                'room' => 'B-201',
                'capacity' => 40,
                'schedules' => [
                    ['day_of_week' => 1, 'start_time' => '08:00', 'end_time' => '10:00'],
                    ['day_of_week' => 3, 'start_time' => '08:00', 'end_time' => '10:00'],
                    // A blank row must be dropped, not fail validation.
                    ['day_of_week' => '', 'start_time' => '', 'end_time' => ''],
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('class_sections', ['section_code' => 'A', 'room' => 'B-201']);
        $this->assertDatabaseCount('class_schedules', 2);
    }

    public function test_students_are_enrolled_in_bulk_without_duplicates(): void
    {
        $section = $this->makeSection();
        $already = $this->makeStudent();
        $fresh = $this->makeStudent();
        $this->enroll($already, $section);

        $this->actingAs($this->admin)
            ->post(route('admin.class-sections.enrollments.store', $section), [
                'student_ids' => [$already->id, $fresh->id],
            ]);

        $this->assertDatabaseCount('enrollments', 2);
    }

    public function test_activating_a_semester_stands_the_others_down(): void
    {
        $old = Semester::factory()->active()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.semesters.store'), [
                'code' => '2026-S3',
                'name' => '2026 Semester 3',
                'start_date' => '2026-09-01',
                'end_date' => '2026-12-20',
                'is_active' => '1',
            ]);

        $this->assertFalse($old->refresh()->is_active);
        $this->assertDatabaseHas('semesters', ['code' => '2026-S3', 'is_active' => true]);
    }

    public function test_a_faculty_still_holding_courses_cannot_be_deleted(): void
    {
        $faculty = Faculty::factory()->create();
        Course::factory()->create(['faculty_id' => $faculty->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.faculties.destroy', $faculty))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('faculties', ['id' => $faculty->id]);
    }

    public function test_an_administrator_cannot_deactivate_their_own_account(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.users.toggle', $this->admin))
            ->assertSessionHas('error');

        $this->assertTrue($this->admin->refresh()->is_active);
    }

    public function test_an_administrator_resets_another_users_password(): void
    {
        $student = $this->makeStudent();

        $this->actingAs($this->admin)
            ->put(route('admin.users.password', $student->user), [
                'password' => 'brand-new-password',
                'password_confirmation' => 'brand-new-password',
            ])
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('brand-new-password', $student->user->refresh()->password));
    }
}
