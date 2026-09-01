<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Semester;
use Illuminate\Database\Seeder;

/**
 * Faculties, programs, courses and semesters for East Asia Management University.
 */
class AcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        $faculties = [
            'FBM' => 'Faculty of Business and Management',
            'FCIT' => 'Faculty of Computing and Information Technology',
            'FENG' => 'Faculty of Engineering',
            'FHS' => 'Faculty of Health Sciences',
        ];

        foreach ($faculties as $code => $name) {
            Faculty::firstOrCreate(['code' => $code], ['name' => $name]);
        }

        $programs = [
            ['FBM', 'BBA', 'BBA Business Administration'],
            ['FBM', 'BACC', 'BSc Accounting and Finance'],
            ['FCIT', 'BSCS', 'BSc Computer Science'],
            ['FCIT', 'BSIT', 'BSc Information Technology'],
            ['FENG', 'BENG', 'BEng Civil Engineering'],
            ['FHS', 'BNUR', 'BSc Nursing'],
        ];

        foreach ($programs as [$facultyCode, $code, $name]) {
            Program::firstOrCreate(['code' => $code], [
                'faculty_id' => Faculty::where('code', $facultyCode)->value('id'),
                'name' => $name,
            ]);
        }

        $courses = [
            ['FCIT', 'CS101', 'Introduction to Programming', 3],
            ['FCIT', 'CS201', 'Data Structures and Algorithms', 4],
            ['FCIT', 'CS210', 'Database Systems', 3],
            ['FCIT', 'IT230', 'Web Application Development', 3],
            ['FCIT', 'IT310', 'Computer Networks', 3],
            ['FBM', 'BM101', 'Principles of Management', 3],
            ['FBM', 'BM220', 'Organisational Behaviour', 3],
            ['FBM', 'AC150', 'Financial Accounting', 4],
            ['FENG', 'EN180', 'Engineering Mathematics', 4],
            ['FHS', 'HS120', 'Human Anatomy and Physiology', 4],
        ];

        foreach ($courses as [$facultyCode, $code, $title, $credits]) {
            Course::firstOrCreate(['code' => $code], [
                'faculty_id' => Faculty::where('code', $facultyCode)->value('id'),
                'title' => $title,
                'credit_hours' => $credits,
                'description' => "{$title} is offered by the ".$faculties[$facultyCode].'.',
            ]);
        }

        // The active semester is anchored to today so the seeded timetable has
        // both past sessions to report on and upcoming ones to demonstrate.
        $activeStart = now()->startOfMonth()->subMonths(2);

        Semester::firstOrCreate(['code' => $activeStart->copy()->subMonths(6)->format('Y').'-S1'], [
            'name' => $activeStart->copy()->subMonths(6)->format('Y').' Semester 1',
            'start_date' => $activeStart->copy()->subMonths(6),
            'end_date' => $activeStart->copy()->subMonths(2),
            'is_active' => false,
        ]);

        Semester::firstOrCreate(['code' => $activeStart->format('Y').'-S2'], [
            'name' => $activeStart->format('Y').' Semester 2',
            'start_date' => $activeStart,
            'end_date' => $activeStart->copy()->addMonths(4),
            'is_active' => true,
        ]);
    }
}
