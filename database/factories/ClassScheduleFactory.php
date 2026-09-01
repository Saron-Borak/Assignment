<?php

namespace Database\Factories;

use App\Models\ClassSchedule;
use App\Models\ClassSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassSchedule>
 */
class ClassScheduleFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->randomElement(['08:00', '10:00', '13:00', '15:00']);

        return [
            'class_section_id' => ClassSection::factory(),
            'day_of_week' => fake()->numberBetween(1, 5),
            'start_time' => $start.':00',
            'end_time' => sprintf('%02d:00:00', ((int) substr($start, 0, 2)) + 2),
            'room' => null,
        ];
    }
}
