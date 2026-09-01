<?php

namespace Database\Seeders;

use App\Enums\EnrollmentStatus;
use App\Models\ClassSection;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lecturer;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Database\Seeder;

/**
 * Class sections with a weekly timetable, plus the student rosters.
 */
class TeachingSeeder extends Seeder
{
    public function run(): void
    {
        $semester = Semester::where('is_active', true)->firstOrFail();

        // course code, section, lecturer email, room, [[day, start, end], ...]
        $sections = [
            ['CS101', 'A', 'v.meas@eamu.edu', 'B-201', [[1, '08:00', '10:00'], [3, '08:00', '10:00']]],
            ['CS101', 'B', 'r.sok@eamu.edu', 'B-202', [[2, '10:00', '12:00'], [4, '10:00', '12:00']]],
            ['CS201', 'A', 'v.meas@eamu.edu', 'B-305', [[2, '08:00', '10:00'], [5, '08:00', '10:00']]],
            ['CS210', 'A', 's.pich@eamu.edu', 'B-301', [[1, '13:00', '15:00'], [3, '13:00', '15:00']]],
            ['IT230', 'A', 's.pich@eamu.edu', 'C-104', [[2, '13:00', '15:00'], [4, '13:00', '15:00']]],
            ['IT310', 'A', 'r.sok@eamu.edu', 'C-210', [[5, '10:00', '12:00']]],
            ['BM101', 'A', 'c.nou@eamu.edu', 'A-101', [[1, '10:00', '12:00'], [3, '10:00', '12:00']]],
            ['BM220', 'A', 'c.nou@eamu.edu', 'A-105', [[4, '08:00', '10:00']]],
            ['AC150', 'A', 'd.kim@eamu.edu', 'A-203', [[2, '15:00', '17:00'], [5, '13:00', '15:00']]],
            ['EN180', 'A', 'b.ly@eamu.edu', 'D-110', [[1, '15:00', '17:00'], [3, '15:00', '17:00']]],
        ];

        foreach ($sections as [$courseCode, $code, $lecturerEmail, $room, $slots]) {
            $section = ClassSection::firstOrCreate([
                'course_id' => Course::where('code', $courseCode)->value('id'),
                'semester_id' => $semester->id,
                'section_code' => $code,
            ], [
                'lecturer_id' => Lecturer::whereRelation('user', 'email', $lecturerEmail)->value('id'),
                'room' => $room,
                'capacity' => 40,
            ]);

            if ($section->schedules()->doesntExist()) {
                foreach ($slots as [$day, $start, $end]) {
                    $section->schedules()->create([
                        'day_of_week' => $day,
                        'start_time' => $start.':00',
                        'end_time' => $end.':00',
                        'room' => $room,
                    ]);
                }
            }
        }

        $this->enrollStudents($semester);
    }

    /**
     * Spread students across sections so most take four or five classes and no
     * section is empty.
     */
    protected function enrollStudents(Semester $semester): void
    {
        $sections = ClassSection::where('semester_id', $semester->id)->orderBy('id')->get();
        $students = Student::orderBy('id')->get();

        if ($sections->isEmpty() || $students->isEmpty()) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($students as $index => $student) {
            // A rotating offset gives each student a different, repeatable
            // combination of four or five classes.
            $take = 4 + ($index % 2);

            for ($n = 0; $n < $take; $n++) {
                $section = $sections[($index * 3 + $n * 2) % $sections->count()];

                $key = $student->id.':'.$section->id;

                if (isset($rows[$key])) {
                    continue;
                }

                $rows[$key] = [
                    'class_section_id' => $section->id,
                    'student_id' => $student->id,
                    'status' => EnrollmentStatus::Enrolled->value,
                    'enrolled_at' => $semester->start_date->toDateString(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // upsert keeps the seeder re-runnable without duplicating a roster.
        Enrollment::upsert(
            array_values($rows),
            ['class_section_id', 'student_id'],
            ['status', 'updated_at'],
        );
    }
}
