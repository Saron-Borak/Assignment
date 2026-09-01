<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSection;
use App\Models\Faculty;
use App\Models\Semester;
use App\Models\Student;
use App\Services\AttendanceReportService;
use App\Support\CsvExporter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request, AttendanceReportService $reports): View
    {
        $semester = $this->resolveSemester($request);

        return view('admin.reports.index', [
            'semester' => $semester,
            'semesters' => Semester::orderByDesc('start_date')->get(),
            'faculties' => Faculty::orderBy('name')->get(),
            'facultyId' => $request->integer('faculty_id') ?: null,
            'totals' => $reports->universityTotals($semester),
            'sections' => $reports->sectionOverview($semester, $request->integer('faculty_id') ?: null),
        ]);
    }

    public function lowAttendance(Request $request, AttendanceReportService $reports): View
    {
        $semester = $this->resolveSemester($request);

        return view('admin.reports.low-attendance', [
            'semester' => $semester,
            'semesters' => Semester::orderByDesc('start_date')->get(),
            'rows' => $reports->lowAttendance($semester),
            'threshold' => config('attendance.min_percentage'),
        ]);
    }

    public function classSection(ClassSection $section, Request $request, AttendanceReportService $reports): View
    {
        $section->load(['course', 'semester', 'lecturer.user', 'schedules']);

        return view('admin.reports.class-section', [
            'section' => $section,
            'from' => $request->date('from')?->toDateString(),
            'to' => $request->date('to')?->toDateString(),
            'stats' => $reports->classSectionStats(
                $section,
                $request->date('from')?->toDateString(),
                $request->date('to')?->toDateString(),
            ),
            'sessions' => $section->sessions()
                ->closed()
                ->betweenDates($request->date('from')?->toDateString(), $request->date('to')?->toDateString())
                ->orderBy('session_date')
                ->get(),
        ]);
    }

    public function student(Student $student, AttendanceReportService $reports): View
    {
        $student->load(['user', 'program.faculty']);

        return view('admin.reports.student', [
            'student' => $student,
            'stats' => $reports->studentOverall($student),
        ]);
    }

    /**
     * Download one section's register as CSV.
     */
    public function exportClassSection(
        ClassSection $section,
        Request $request,
        AttendanceReportService $reports,
        CsvExporter $csv,
    ): StreamedResponse {
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

    /**
     * Download the at-risk list as CSV.
     */
    public function exportLowAttendance(
        Request $request,
        AttendanceReportService $reports,
        CsvExporter $csv,
    ): StreamedResponse {
        $semester = $this->resolveSemester($request);
        $rows = $reports->lowAttendance($semester);

        return $csv->download(
            $csv->filename('at-risk-students', $semester?->code),
            ['Student No', 'Name', 'Email', 'Program', 'Course', 'Section', 'Attended', 'Countable', 'Absent', 'Attendance %'],
            $rows->map(fn ($row) => [
                $row->student_no,
                $row->name,
                $row->email,
                $row->program_name,
                $row->course_code,
                $row->section_code,
                $row->attended,
                $row->countable,
                $row->absent,
                number_format($row->percentage, 1),
            ]),
        );
    }

    /**
     * Download one student's record across every class as CSV.
     */
    public function exportStudent(
        Student $student,
        AttendanceReportService $reports,
        CsvExporter $csv,
    ): StreamedResponse {
        $student->load('user');

        return $csv->download(
            $csv->filename('attendance', $student->student_no),
            ['Course', 'Section', 'Title', 'Lecturer', 'Semester', 'Sessions Held', 'Present', 'Late', 'Absent', 'Excused', 'Attendance %', 'At Risk'],
            $reports->studentOverall($student)->map(fn ($row) => [
                $row->course_code,
                $row->section_code,
                $row->course_title,
                $row->lecturer_name,
                $row->semester_name,
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

    protected function resolveSemester(Request $request): ?Semester
    {
        if ($id = $request->integer('semester_id')) {
            return Semester::find($id);
        }

        return Semester::current();
    }
}
