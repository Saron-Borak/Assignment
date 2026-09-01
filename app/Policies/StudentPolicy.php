<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    /**
     * Students may only ever read their own record; lecturers may read the
     * record of anyone enrolled in one of their sections.
     */
    public function view(User $user, Student $student): bool
    {
        if ($user->isStudent()) {
            return $user->student?->id === $student->id;
        }

        if ($user->isLecturer() && $lecturer = $user->lecturer) {
            return $student->enrollments()
                ->whereHas('classSection', fn ($q) => $q->where('lecturer_id', $lecturer->id))
                ->exists();
        }

        return false;
    }
}
