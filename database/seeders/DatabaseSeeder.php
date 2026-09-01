<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\ClassSection;
use App\Models\Student;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AcademicStructureSeeder::class,
            PeopleSeeder::class,
            TeachingSeeder::class,
            AttendanceHistorySeeder::class,
        ]);

        $this->summarise();
    }

    protected function summarise(): void
    {
        $this->command?->newLine();
        $this->command?->info('East Asia Management University - Attendance System seeded.');
        $this->command?->newLine();

        $this->command?->table(
            ['Records', 'Count'],
            [
                ['Students', Student::count()],
                ['Class sections', ClassSection::count()],
                ['Sessions', AttendanceSession::count()],
                ['Attendance records', AttendanceRecord::count()],
            ],
        );

        $this->command?->line('  Sign in with any of these accounts (password: <fg=yellow>password</>)');
        $this->command?->newLine();

        $this->command?->table(
            ['Role', 'Email'],
            [
                ['Administrator', 'admin@eamu.edu'],
                ['Lecturer', 'v.meas@eamu.edu'],
                ['Lecturer', 's.pich@eamu.edu'],
                ['Student', Student::with('user')->orderBy('id')->first()?->user->email ?? '-'],
            ],
        );
    }
}
