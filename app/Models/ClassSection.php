<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['course_id', 'semester_id', 'lecturer_id', 'section_code', 'room', 'capacity'])]
class ClassSection extends Model
{
    use HasFactory;

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }

    /**
     * Actively enrolled students, ordered the way a printed register would be.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'enrollments')
            ->withPivot(['status', 'enrolled_at'])
            ->wherePivot('status', EnrollmentStatus::Enrolled->value)
            ->withTimestamps();
    }

    public function label(): string
    {
        return "{$this->course->code}-{$this->section_code}";
    }

    public function fullLabel(): string
    {
        return "{$this->course->code}-{$this->section_code} — {$this->course->title}";
    }

    /** @param  Builder<ClassSection>  $query */
    public function scopeForLecturer(Builder $query, Lecturer $lecturer): void
    {
        $query->where('lecturer_id', $lecturer->id);
    }

    /** @param  Builder<ClassSection>  $query */
    public function scopeInSemester(Builder $query, ?Semester $semester): void
    {
        $query->when($semester, fn (Builder $q) => $q->where('semester_id', $semester->id));
    }
}
