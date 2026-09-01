<?php

namespace Database\Seeders;

use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Models\Faculty;
use App\Models\Lecturer;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * The administrator, teaching staff and the student body.
 *
 * Every seeded account uses the password "password".
 */
class PeopleSeeder extends Seeder
{
    public const PASSWORD = 'password';

    public const STUDENT_COUNT = 60;

    public function run(): void
    {
        // Hash once - bcrypt is deliberately slow and this seeder creates ~70
        // accounts.
        $password = Hash::make(self::PASSWORD);

        User::updateOrCreate(['email' => 'admin@eamu.edu'], [
            'name' => 'Sokha Chan',
            'password' => $password,
            'role' => UserRole::Admin,
            'phone' => '023 900 100',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $lecturers = [
            ['Dr.', 'Vannak Meas', 'v.meas@eamu.edu', 'FCIT'],
            ['Dr.', 'Sreymom Pich', 's.pich@eamu.edu', 'FCIT'],
            ['Mr.', 'Rithy Sok', 'r.sok@eamu.edu', 'FCIT'],
            ['Prof.', 'Chanthou Nou', 'c.nou@eamu.edu', 'FBM'],
            ['Ms.', 'Dara Kim', 'd.kim@eamu.edu', 'FBM'],
            ['Dr.', 'Bopha Ly', 'b.ly@eamu.edu', 'FENG'],
        ];

        foreach ($lecturers as $index => [$title, $name, $email, $facultyCode]) {
            $user = User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => $password,
                'role' => UserRole::Lecturer,
                'phone' => '012 '.str_pad((string) (100 + $index), 3, '0', STR_PAD_LEFT).' '.str_pad((string) (400 + $index), 3, '0', STR_PAD_LEFT),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            Lecturer::updateOrCreate(['user_id' => $user->id], [
                'faculty_id' => Faculty::where('code', $facultyCode)->value('id'),
                'staff_no' => 'EAMU-L-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'title' => $title,
            ]);
        }

        $this->seedStudents($password);
    }

    protected function seedStudents(string $password): void
    {
        if (Student::count() >= self::STUDENT_COUNT) {
            return;
        }

        $given = [
            'Sophea', 'Panha', 'Chanda', 'Vichea', 'Kanya', 'Rasmey', 'Sokun', 'Theary',
            'Makara', 'Sovann', 'Pisey', 'Nita', 'Samnang', 'Leakhena', 'Kosal', 'Davy',
            'Piseth', 'Sreypov', 'Chhaya', 'Veasna',
        ];

        $family = [
            'Chea', 'Sok', 'Nguon', 'Heng', 'Ros', 'Ouk', 'Yim', 'Sam',
            'Khoun', 'Prak', 'Tep', 'Mao', 'Nhem', 'Chhim', 'Sar',
        ];

        $programs = Program::pluck('id')->all();
        $used = [];

        for ($i = 1; $i <= self::STUDENT_COUNT; $i++) {
            // Deterministic name selection keeps re-runs reproducible.
            $name = $given[($i * 7) % count($given)].' '.$family[($i * 5) % count($family)];

            // The name pool is small enough to repeat, so a suffix is added the
            // second and subsequent times a slug appears.
            $slug = Str::of($name)->lower()->replace(' ', '.')->toString();
            $seen = $used[$slug] = ($used[$slug] ?? 0) + 1;
            $email = $slug.($seen > 1 ? '.'.$seen : '').'@student.eamu.edu';

            $intake = 2023 + ($i % 4);

            $user = User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => $password,
                'role' => UserRole::Student,
                'phone' => '011 '.str_pad((string) (200 + $i), 3, '0', STR_PAD_LEFT).' '.str_pad((string) (300 + $i), 3, '0', STR_PAD_LEFT),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            Student::updateOrCreate(['user_id' => $user->id], [
                'program_id' => $programs[$i % count($programs)],
                'student_no' => "EAMU-{$intake}-".str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'intake_year' => $intake,
                'status' => StudentStatus::Active,
            ]);
        }
    }
}
