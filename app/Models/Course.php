<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['faculty_id', 'code', 'title', 'credit_hours', 'description'])]
class Course extends Model
{
    use HasFactory;

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function classSections(): HasMany
    {
        return $this->hasMany(ClassSection::class);
    }

    public function label(): string
    {
        return "{$this->code} — {$this->title}";
    }

    /** @param  Builder<Course>  $query */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $query->when($term, fn (Builder $q) => $q->where(
            fn (Builder $inner) => $inner
                ->where('code', 'like', "%{$term}%")
                ->orWhere('title', 'like', "%{$term}%")
        ));
    }
}
