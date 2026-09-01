<?php

namespace App\Http\Controllers\Lecturer;

use App\Enums\AttendanceStatus;
use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\ClassSection;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function __construct(protected AttendanceService $attendance) {}

    public function index(Request $request): View
    {
        $lecturer = auth()->user()->lecturer;

        $sections = ClassSection::with('course')->forLecturer($lecturer)->get();

        $sessions = AttendanceSession::with('classSection.course')
            ->withCount([
                'records',
                'records as attended_count' => fn ($q) => $q->attended(),
            ])
            ->whereIn('class_section_id', $sections->pluck('id'))
            ->when($request->integer('section_id'), fn ($q, $id) => $q->where('class_section_id', $id))
            ->when(
                $request->filled('status'),
                fn ($q) => $q->where('status', SessionStatus::from($request->string('status')->toString()))
            )
            ->betweenDates($request->date('from')?->toDateString(), $request->date('to')?->toDateString())
            ->orderByDesc('session_date')
            ->orderByDesc('start_time')
            ->paginate(20)
            ->withQueryString();

        return view('lecturer.sessions.index', compact('sessions', 'sections'));
    }

    public function create(ClassSection $section): View
    {
        $this->authorize('teach', $section);

        $section->load(['course', 'semester', 'schedules']);

        return view('lecturer.sessions.create', compact('section'));
    }

    public function store(Request $request, ClassSection $section): RedirectResponse
    {
        $this->authorize('teach', $section);

        $validated = $request->validate([
            'session_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'topic' => ['nullable', 'string', 'max:255'],
        ]);

        $exists = $section->sessions()
            ->whereDate('session_date', $validated['session_date'])
            ->where('start_time', $validated['start_time'].':00')
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'A session already exists for that date and time.');
        }

        $session = $section->sessions()->create([
            'session_date' => $validated['session_date'],
            'start_time' => $validated['start_time'].':00',
            'end_time' => $validated['end_time'].':00',
            'topic' => $validated['topic'] ?? null,
            'status' => SessionStatus::Scheduled,
            'late_after_minutes' => (int) config('attendance.late_after_minutes'),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('lecturer.sessions.show', $session)->with('success', 'Session created.');
    }

    /**
     * Bulk-create the whole semester from the section timetable.
     */
    public function generate(Request $request, ClassSection $section): RedirectResponse
    {
        $this->authorize('teach', $section);

        $validated = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        if ($section->schedules()->doesntExist()) {
            return back()->with('error', 'This section has no timetable, so sessions cannot be generated. Ask an administrator to add one.');
        }

        $created = $this->attendance->generateSessions(
            $section,
            \Illuminate\Support\Carbon::parse($validated['from']),
            \Illuminate\Support\Carbon::parse($validated['to']),
            $request->user(),
        );

        return redirect()
            ->route('lecturer.classes.show', $section)
            ->with($created > 0 ? 'success' : 'warning', $created > 0
                ? "Generated {$created} session(s) from the timetable."
                : 'No new sessions were needed for that date range.');
    }

    public function show(AttendanceSession $session): View
    {
        $this->authorize('view', $session);

        $session->load(['classSection.course', 'classSection.semester', 'creator']);

        return view('lecturer.sessions.show', [
            'session' => $session,
            'records' => $session->records()
                ->with(['student.user', 'marker'])
                ->get()
                ->sortBy('student.user.name'),
        ]);
    }

    /**
     * The marking register: every enrolled student with their current mark
     * pre-selected.
     */
    public function mark(AttendanceSession $session): View
    {
        $this->authorize('manage', $session);

        $session->load(['classSection.course', 'classSection.semester']);

        $existing = $session->records()->get()->keyBy('student_id');

        $roster = $session->classSection
            ->students()
            ->with(['user', 'program'])
            ->get()
            ->sortBy('user.name')
            ->values();

        return view('lecturer.sessions.mark', [
            'session' => $session,
            'roster' => $roster,
            'existing' => $existing,
            'statuses' => AttendanceStatus::cases(),
        ]);
    }

    public function storeMarks(Request $request, AttendanceSession $session): RedirectResponse
    {
        $this->authorize('manage', $session);

        $validated = $request->validate([
            'marks' => ['required', 'array'],
            'marks.*' => ['required', Rule::in(array_column(AttendanceStatus::cases(), 'value'))],
            'remarks' => ['array'],
            'remarks.*' => ['nullable', 'string', 'max:255'],
            'then' => ['nullable', 'in:close'],
        ]);

        $saved = $this->attendance->saveMarks(
            $session,
            $validated['marks'],
            $request->user(),
            $validated['remarks'] ?? [],
        );

        if (($validated['then'] ?? null) === 'close' && ! $session->isClosed()) {
            $this->attendance->closeSession($session->refresh(), $request->user());

            return redirect()
                ->route('lecturer.sessions.show', $session)
                ->with('success', "Register saved for {$saved} student(s) and the session was closed.");
        }

        return redirect()
            ->route('lecturer.sessions.show', $session)
            ->with('success', "Register saved for {$saved} student(s).");
    }

    public function open(Request $request, AttendanceSession $session): RedirectResponse
    {
        $this->authorize('manage', $session);

        if ($session->classSection->enrollments()->active()->doesntExist()) {
            return back()->with('error', 'No students are enrolled in this class yet.');
        }

        $this->attendance->openSession($session, $request->user());

        return redirect()
            ->route('lecturer.sessions.qr', $session)
            ->with('success', 'Session opened. Students can now check in.');
    }

    public function close(Request $request, AttendanceSession $session): RedirectResponse
    {
        $this->authorize('manage', $session);

        $absent = $this->attendance->closeSession($session, $request->user());

        return redirect()
            ->route('lecturer.sessions.show', $session)
            ->with('success', $absent > 0
                ? "Session closed. {$absent} student(s) who never checked in were marked absent."
                : 'Session closed. Every student was already marked.');
    }

    public function destroy(AttendanceSession $session): RedirectResponse
    {
        $this->authorize('manage', $session);

        if ($session->records()->exists()) {
            return back()->with('error', 'This session already has attendance recorded and cannot be deleted.');
        }

        $section = $session->classSection;
        $session->delete();

        return redirect()->route('lecturer.classes.show', $section)->with('success', 'Session deleted.');
    }
}
