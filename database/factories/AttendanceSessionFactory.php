<?php

namespace Database\Factories;

use App\Enums\SessionStatus;
use App\Models\AttendanceSession;
use App\Models\ClassSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceSession>
 */
class AttendanceSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'class_section_id' => ClassSection::factory(),
            'session_date' => now()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'topic' => ucfirst(fake()->words(3, true)),
            'status' => SessionStatus::Scheduled,
            'late_after_minutes' => config('attendance.late_after_minutes'),
        ];
    }

    public function open(): static
    {
        return $this->state(fn () => [
            'status' => SessionStatus::Open,
            'opened_at' => now(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'status' => SessionStatus::Closed,
            'opened_at' => now()->subHours(2),
            'closed_at' => now(),
        ]);
    }
}
