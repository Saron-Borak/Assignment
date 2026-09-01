<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Late = 'late';
    case Absent = 'absent';
    case Excused = 'excused';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * Whether the student was in the room. Late attendance only counts when the
     * university policy says it should.
     */
    public function countsAsAttended(): bool
    {
        return match ($this) {
            self::Present => true,
            self::Late => (bool) config('attendance.count_late_as_present'),
            self::Absent, self::Excused => false,
        };
    }

    /**
     * Excused absences are removed from the denominator rather than counted
     * against the student.
     */
    public function countsTowardsTotal(): bool
    {
        return $this !== self::Excused;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Present => 'text-bg-success',
            self::Late => 'text-bg-warning',
            self::Absent => 'text-bg-danger',
            self::Excused => 'text-bg-secondary',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Present => 'bi-check-circle-fill',
            self::Late => 'bi-clock-fill',
            self::Absent => 'bi-x-circle-fill',
            self::Excused => 'bi-info-circle-fill',
        };
    }
}
