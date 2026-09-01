<?php

namespace App\Http\Controllers;

use App\Enums\MarkedVia;
use App\Exceptions\CheckInException;
use App\Models\AttendanceSession;
use App\Models\Student;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Student-facing self check-in, reached either by scanning the projected QR
 * code or by typing the six-character code shown beside it.
 */
class CheckInController extends Controller
{
    public function __construct(protected AttendanceService $attendance) {}

    public function create(): View
    {
        return view('student.check-in', [
            'recent' => $this->student()->attendanceRecords()
                ->with('session.classSection.course')
                ->whereIn('marked_via', [MarkedVia::Qr, MarkedVia::Code])
                ->latest('marked_at')
                ->limit(5)
                ->get(),
        ]);
    }

    /**
     * Landing point for the scanned QR code.
     */
    public function viaToken(string $token): RedirectResponse
    {
        return $this->attempt(
            fn () => $this->attendance->resolveByToken($token),
            MarkedVia::Qr,
        );
    }

    /**
     * Fallback for students without a working camera.
     */
    public function viaCode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [], ['code' => 'check-in code']);

        return $this->attempt(
            fn () => $this->attendance->resolveByCode($validated['code']),
            MarkedVia::Code,
        );
    }

    /**
     * Resolve the session, record the check-in, and turn any domain failure into
     * a flash message the student can act on.
     */
    protected function attempt(callable $resolve, MarkedVia $via): RedirectResponse
    {
        try {
            /** @var AttendanceSession $session */
            $session = $resolve();

            $record = $this->attendance->checkIn($this->student(), $session, $via);

            $course = $session->classSection->course->code;

            return redirect()
                ->route('student.dashboard')
                ->with('success', "Checked in to {$course} as {$record->status->label()}.");
        } catch (CheckInException $e) {
            return redirect()
                ->route('student.check-in.create')
                ->with('error', $e->getMessage());
        }
    }

    protected function student(): Student
    {
        $student = auth()->user()->student;

        if (! $student) {
            throw CheckInException::noStudentProfile();
        }

        return $student;
    }
}
