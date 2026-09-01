<?php

namespace Database\Factories;

use App\Enums\StudentStatus;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    public function definition(): array
    {
        $intake = fake()->numberBetween(2023, 2026);

        return [
            'user_id' => User::factory()->student(),
            'program_id' => Program::factory(),
            'student_no' => "EAMU-{$intake}-".fake()->unique()->numerify('####'),
            'intake_year' => $intake,
            'status' => StudentStatus::Active,
        ];
    }

    public function graduated(): static
    {
        return $this->state(fn () => ['status' => StudentStatus::Graduated]);
    }
}
