<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['class_section_id', 'day_of_week', 'start_time', 'end_time', 'room'])]
class ClassSchedule extends Model
{
    use HasFactory;

    /** ISO-8601 day numbering, matching Carbon dayOfWeekIso. */
    public const DAYS = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
        ];
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function dayName(): string
    {
        return self::DAYS[$this->day_of_week] ?? 'Unknown';
    }

    public function shortDayName(): string
    {
        return substr($this->dayName(), 0, 3);
    }

    public function timeRange(): string
    {
        return substr((string) $this->start_time, 0, 5).' - '.substr((string) $this->end_time, 0, 5);
    }
}
