<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

/**
 * Raised when a self check-in is refused. The message is written for the
 * student, so it is safe to show directly in a flash alert.
 */
class CheckInException extends RuntimeException
{
    public static function invalidToken(): self
    {
        return new self('That check-in code is not valid. Ask your lecturer for the current code.');
    }

    public static function expired(): self
    {
        return new self('That check-in code has expired. Scan the code currently on screen.');
    }

    public static function sessionNotOpen(): self
    {
        return new self('This class session is not open for check-in.');
    }

    public static function notEnrolled(): self
    {
        return new self('You are not enrolled in this class, so you cannot check in.');
    }

    public static function alreadyCheckedIn(string $status): self
    {
        return new self("You have already been marked {$status} for this session.");
    }

    public static function noStudentProfile(): self
    {
        return new self('Your account is not linked to a student record. Please contact the registry.');
    }

    /**
     * Safety net for the paths that do not catch this explicitly - a student
     * should see a flash message, never a stack trace.
     */
    public function render(Request $request): RedirectResponse
    {
        return redirect()
            ->route($request->user()?->isStudent() ? 'student.check-in.create' : 'login')
            ->with('error', $this->getMessage());
    }
}
