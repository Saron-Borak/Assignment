<?php

namespace Database\Factories;

use App\Models\Faculty;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    public function definition(): array
    {
        return [
            'faculty_id' => Faculty::factory(),
            'code' => strtoupper(fake()->unique()->lexify('P???')),
            'name' => 'BSc '.fake()->words(2, true),
        ];
    }
}
