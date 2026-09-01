<?php

namespace App\Policies;

use App\Models\AttendanceSession;
use App\Models\User;

class AttendanceSessionPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, AttendanceSession $session): bool
    {
        return $this->owns($user, $session);
    }

    /**
     * Only the assigned lecturer may open, mark or close a register. Route
     * middleware alone would let one lecturer edit another's session by
     * guessing an id, so the ownership test lives here.
     */
    public function manage(User $user, AttendanceSession $session): bool
    {
        return $this->owns($user, $session);
    }

    protected function owns(User $user, AttendanceSession $session): bool
    {
        return $user->isLecturer()
            && $user->lecturer?->id === $session->classSection->lecturer_id;
    }
}
