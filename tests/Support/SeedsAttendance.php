<?php

namespace Tests\Support;

use App\Enums\EnrollmentStatus;
use App\Models\ClassSection;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\Lecturer;
use App\Models\Program;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;

/**
 * Builds the smallest complete slice of the domain a test needs: a faculty, a
 * semester, a lecturer, a section and some enrolled students.
 */
trait SeedsAttendance
{
    protected Faculty $faculty;

    protected Semester $semester;

    protected function makeFaculty(): Faculty
    {
        return $this->faculty ??= Faculty::factory()->create(['code' => 'FCIT']);
    }

    protected function makeSemester(): Semester
    {
        return $this->semester ??= Semester::factory()->active()->create([
            'start_date' => now()->subMonths(2),
            'end_date' => now()->addMonths(2),
        ]);
    }

    protected function makeLecturer(?string $email = null): Lecturer
    {
        return Lecturer::factory()->create([
            'faculty_id' => $this->makeFaculty()->id,
            'user_id' => User::factory()->lecturer()->create(
                $email ? ['email' => $email] : []
            )->id,
        ]);
    }

    protected function makeStudent(?string $email = null): Student
    {
        return Student::factory()->create([
            'program_id' => Program::factory()->create(['faculty_id' => $this->makeFaculty()->id])->id,
            'user_id' => User::factory()->student()->create(
                $email ? ['email' => $email] : []
            )->id,
        ]);
    }

    protected function makeSection(?Lecturer $lecturer = null): ClassSection
    {
        return ClassSection::factory()->create([
            'course_id' => Course::factory()->create(['faculty_id' => $this->makeFaculty()->id])->id,
            'semester_id' => $this->makeSemester()->id,
            'lecturer_id' => ($lecturer ?? $this->makeLecturer())->id,
        ]);
    }

    protected function enroll(Student $student, ClassSection $section): Enrollment
    {
        return Enrollment::factory()->create([
            'class_section_id' => $section->id,
            'student_id' => $student->id,
            'status' => EnrollmentStatus::Enrolled,
        ]);
    }
}
