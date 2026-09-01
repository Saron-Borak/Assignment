<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use App\Enums\StudentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'program_id', 'student_no', 'intake_year', 'status'])]
class Student extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => StudentStatus::class,
            'intake_year' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Sections the student is currently enrolled in (dropped ones excluded).
     */
    public function classSections(): BelongsToMany
    {
        return $this->belongsToMany(ClassSection::class, 'enrollments')
            ->withPivot(['status', 'enrolled_at'])
            ->wherePivot('status', EnrollmentStatus::Enrolled->value)
            ->withTimestamps();
    }

    public function isEnrolledIn(ClassSection $section): bool
    {
        return $this->enrollments()
            ->where('class_section_id', $section->id)
            ->where('status', EnrollmentStatus::Enrolled)
            ->exists();
    }

    /** @param  Builder<Student>  $query */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $query->when($term, fn (Builder $q) => $q->where(
            fn (Builder $inner) => $inner
                ->where('student_no', 'like', "%{$term}%")
                ->orWhereHas('user', fn (Builder $u) => $u
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"))
        ));
    }
}
