<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'is_active', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function lecturer(): HasOne
    {
        return $this->hasOne(Lecturer::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isLecturer(): bool
    {
        return $this->role === UserRole::Lecturer;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }

    /**
     * Initials used by the avatar bubble in the navigation bar.
     */
    public function initials(): string
    {
        preg_match_all('/\b\p{L}/u', $this->name, $matches);

        return mb_strtoupper(implode('', array_slice($matches[0], 0, 2)));
    }

    /** @param  Builder<User>  $query */
    public function scopeRole(Builder $query, UserRole $role): void
    {
        $query->where('role', $role);
    }

    /** @param  Builder<User>  $query */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $query->when($term, fn (Builder $q) => $q->where(
            fn (Builder $inner) => $inner
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
        ));
    }
}
