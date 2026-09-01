<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\ClassSection;
use App\Models\Semester;
use App\Services\AttendanceReportService;
use App\Support\CsvExporter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClassSectionController extends Controller
{
    public function index(Request $request): View
    {
        $lecturer = auth()->user()->lecturer;
        $semester = $request->integer('semester_id')
            ? Semester::find($request->integer('semester_id'))
            : Semester::current();

        return view('lecturer.classes.index', [
            'semester' => $semester,
            'semesters' => Semester::orderByDesc('start_date')->get(),
            'sections' => ClassSection::with(['course', 'semester', 'schedules'])
                ->withCount([
                    'enrollments as students_count' => fn ($q) => $q->active(),
                    'sessions',
                    'sessions as closed_sessions_count' => fn ($q) => $q->closed(),
                ])
                ->forLecturer($lecturer)
                ->inSemester($semester)
                ->get()
                ->sortBy(fn (ClassSection $s) => $s->course->code),
        ]);
    }

    public function show(ClassSection $section, AttendanceReportService $reports): View
    {
        // Middleware proves the user is a lecturer; the policy proves it is
        // THIS lecturer's section.
        $this->authorize('teach', $section);

        $section->load(['course.faculty', 'semester', 'schedules']);

        return view('lecturer.classes.show', [
            'section' => $section,
            'stats' => $reports->classSectionStats($section),
            'sessions' => $section->sessions()
                ->withCount('records')
                ->orderByDesc('session_date')
                ->orderByDesc('start_time')
                ->paginate(15),
        ]);
    }

    public function report(ClassSection $section, Request $request, AttendanceReportService $reports): View
    {
        $this->authorize('teach', $section);

        $section->load(['course', 'semester', 'schedules']);

        $from = $request->date('from')?->toDateString();
        $to = $request->date('to')?->toDateString();

        return view('lecturer.reports.class', [
            'section' => $section,
            'from' => $from,
            'to' => $to,
            'stats' => $reports->classSectionStats($section, $from, $to),
            'sessions' => $section->sessions()
                ->closed()
                ->betweenDates($from, $to)
                ->orderBy('session_date')
                ->get(),
        ]);
    }

    /**
     * Download this class register as CSV, honouring the same date filters as
     * the on-screen report.
     */
    public function exportReport(
        ClassSection $section,
        Request $request,
        AttendanceReportService $reports,
        CsvExporter $csv,
    ): StreamedResponse {
        $this->authorize('teach', $section);

        $section->load('course');

        $stats = $reports->classSectionStats(
            $section,
            $request->date('from')?->toDateString(),
            $request->date('to')?->toDateString(),
        );

        return $csv->download(
            $csv->filename('attendance', $section->label()),
            ['Student No', 'Name', 'Email', 'Sessions Held', 'Present', 'Late', 'Absent', 'Excused', 'Attendance %', 'At Risk'],
            $stats->map(fn ($row) => [
                $row->student_no,
                $row->name,
                $row->email,
                $row->held,
                $row->present,
                $row->late,
                $row->absent,
                $row->excused,
                number_format($row->percentage, 1),
                $row->at_risk ? 'Yes' : 'No',
            ]),
        );
    }
}
