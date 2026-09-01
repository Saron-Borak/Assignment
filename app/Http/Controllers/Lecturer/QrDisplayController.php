<?php

namespace App\Http\Controllers\Lecturer;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Services\AttendanceService;
use App\Support\QrRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * The projected check-in screen and the polling endpoint that keeps it alive.
 */
class QrDisplayController extends Controller
{
    public function __construct(
        protected AttendanceService $attendance,
        protected QrRenderer $qr,
    ) {}

    public function show(AttendanceSession $session): View|RedirectResponse
    {
        $this->authorize('manage', $session);

        if (! $session->isOpen()) {
            return redirect()
                ->route('lecturer.sessions.show', $session)
                ->with('error', 'Open the session before displaying its check-in code.');
        }

        // A token that lapsed while nobody was watching is replaced on arrival.
        if (! $session->qrIsValid()) {
            $this->attendance->rotateQr($session);
        }

        $session->load(['classSection.course', 'classSection.semester']);

        return view('lecturer.sessions.qr', [
            'session' => $session,
            'payload' => $this->payload($session),
            'refreshSeconds' => (int) config('attendance.qr_refresh_seconds'),
            // Phones cannot resolve a loopback address, so warn when APP_URL
            // would produce an unscannable code.
            'localhostWarning' => $this->targetsLoopback(),
        ]);
    }

    /**
     * Polled by the kiosk: rotates the token and returns the live roster state.
     */
    public function refresh(AttendanceSession $session): JsonResponse
    {
        $this->authorize('manage', $session);

        if (! $session->isOpen()) {
            return response()->json(['closed' => true]);
        }

        $this->attendance->rotateQr($session);

        return response()->json($this->payload($session->refresh()));
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(AttendanceSession $session): array
    {
        $url = route('checkin.token', $session->qr_token);

        $records = $session->records()
            ->with('student.user')
            ->orderByDesc('marked_at')
            ->get();

        return [
            'qr_svg' => $this->qr->svg($url),
            'url' => $url,
            'code' => $session->checkin_code,
            'expires_in' => $session->secondsUntilQrExpires(),
            'present' => $records->where('status', AttendanceStatus::Present)->count(),
            'late' => $records->where('status', AttendanceStatus::Late)->count(),
            'checked_in' => $records->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late])->count(),
            'total' => $session->classSection->enrollments()->active()->count(),
            'recent' => $records->take(12)->map(fn ($record) => [
                'name' => $record->student->user->name,
                'status' => $record->status->value,
                'label' => $record->status->label(),
                'at' => $record->marked_at?->format('H:i:s'),
            ])->values(),
        ];
    }

    protected function targetsLoopback(): bool
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }
}
