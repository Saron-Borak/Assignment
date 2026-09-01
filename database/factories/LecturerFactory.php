<?php

namespace Database\Factories;

use App\Models\Faculty;
use App\Models\Lecturer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lecturer>
 */
class LecturerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->lecturer(),
            'faculty_id' => Faculty::factory(),
            'staff_no' => 'EAMU-L-'.fake()->unique()->numerify('####'),
            'title' => fake()->randomElement(['Dr.', 'Prof.', 'Mr.', 'Ms.']),
        ];
    }
}
