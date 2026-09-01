<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClassSectionRequest;
use App\Models\ClassSection;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Semester;
use App\Services\AttendanceReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClassSectionController extends Controller
{
    public function index(Request $request): View
    {
        $sections = ClassSection::with(['course', 'semester', 'lecturer.user', 'schedules'])
            ->withCount([
                'enrollments as students_count' => fn ($q) => $q->active(),
                'sessions',
            ])
            ->when($request->integer('semester_id'), fn ($q, $id) => $q->where('semester_id', $id))
            ->when($request->integer('lecturer_id'), fn ($q, $id) => $q->where('lecturer_id', $id))
            ->when($request->string('q')->toString(), fn ($q, $term) => $q
                ->whereHas('course', fn ($c) => $c
                    ->where('code', 'like', "%{$term}%")
                    ->orWhere('title', 'like', "%{$term}%")))
            ->orderBy(Course::select('code')->whereColumn('courses.id', 'class_sections.course_id'))
            ->orderBy('section_code')
            ->paginate(15)
            ->withQueryString();

        return view('admin.class-sections.index', [
            'sections' => $sections,
            'semesters' => Semester::orderByDesc('start_date')->get(),
            'lecturers' => Lecturer::with('user')->get()->sortBy('user.name'),
        ]);
    }

    public function create(): View
    {
        return view('admin.class-sections.create', [
            'section' => new ClassSection(['capacity' => 40]),
            'schedules' => [],
        ] + $this->formOptions());
    }

    public function store(ClassSectionRequest $request): RedirectResponse
    {
        $section = DB::transaction(function () use ($request) {
            $section = ClassSection::create($request->safe()->except('schedules'));
            $section->schedules()->createMany($request->schedules());

            return $section;
        });

        return redirect()
            ->route('admin.class-sections.show', $section)
            ->with('success', 'Class section created. Next, enroll students onto the roster.');
    }

    public function show(ClassSection $section, AttendanceReportService $reports): View
    {
        $section->load(['course.faculty', 'semester', 'lecturer.user', 'schedules']);

        return view('admin.class-sections.show', [
            'section' => $section,
            'stats' => $reports->classSectionStats($section),
            'sessions' => $section->sessions()
                ->withCount('records')
                ->orderByDesc('session_date')
                ->orderByDesc('start_time')
                ->limit(10)
                ->get(),
            'sessionCount' => $section->sessions()->count(),
        ]);
    }

    public function edit(ClassSection $section): View
    {
        $section->load('schedules');

        return view('admin.class-sections.edit', [
            'section' => $section,
            'schedules' => $section->schedules->toArray(),
        ] + $this->formOptions());
    }

    public function update(ClassSectionRequest $request, ClassSection $section): RedirectResponse
    {
        DB::transaction(function () use ($request, $section) {
            $section->update($request->safe()->except('schedules'));

            // The timetable is replaced wholesale - it is a short list and this
            // avoids diffing rows the admin may have removed.
            $section->schedules()->delete();
            $section->schedules()->createMany($request->schedules());
        });

        return redirect()->route('admin.class-sections.show', $section)->with('success', 'Class section updated.');
    }

    public function destroy(ClassSection $section): RedirectResponse
    {
        if ($section->sessions()->exists()) {
            return back()->with('error', 'This section has attendance sessions recorded and cannot be deleted.');
        }

        $section->delete();

        return redirect()->route('admin.class-sections.index')->with('success', 'Class section deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'courses' => Course::orderBy('code')->get(),
            'semesters' => Semester::orderByDesc('start_date')->get(),
            'lecturers' => Lecturer::with('user')->get()->sortBy('user.name'),
        ];
    }
}
