<?php

namespace Database\Factories;

use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Semester>
 */
class SemesterFactory extends Factory
{
    public function definition(): array
    {
        $start = now()->startOfMonth()->subMonths(3);

        return [
            'code' => fake()->unique()->numerify('20##-S#'),
            'name' => 'Semester '.fake()->numberBetween(1, 2),
            'start_date' => $start,
            'end_date' => $start->copy()->addMonths(4),
            'is_active' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['is_active' => true]);
    }
}
