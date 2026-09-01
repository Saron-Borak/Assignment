<?php

namespace Database\Factories;

use App\Models\ClassSection;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassSection>
 */
class ClassSectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'semester_id' => Semester::factory(),
            'lecturer_id' => Lecturer::factory(),
            'section_code' => fake()->randomElement(['A', 'B', 'C']),
            'room' => fake()->randomElement(['A', 'B', 'C']).'-'.fake()->numberBetween(101, 405),
            'capacity' => fake()->randomElement([30, 40, 50]),
        ];
    }
}
