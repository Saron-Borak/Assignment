<?php

namespace App\Policies;

use App\Models\ClassSection;
use App\Models\User;

class ClassSectionPolicy
{
    /**
     * Admins have blanket access; every other check falls through to the
     * specific ability below.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    /**
     * A lecturer may only work with sections they are assigned to teach.
     */
    public function view(User $user, ClassSection $section): bool
    {
        if ($user->isLecturer()) {
            return $user->lecturer?->id === $section->lecturer_id;
        }

        if ($user->isStudent()) {
            return $user->student?->isEnrolledIn($section) ?? false;
        }

        return false;
    }

    public function teach(User $user, ClassSection $section): bool
    {
        return $user->isLecturer() && $user->lecturer?->id === $section->lecturer_id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ClassSection $section): bool
    {
        return false;
    }

    public function delete(User $user, ClassSection $section): bool
    {
        return false;
    }
}
