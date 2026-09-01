<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassSection;
use App\Models\Semester;
use App\Services\AttendanceReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request, AttendanceReportService $reports): View
    {
        $student = auth()->user()->student;

        $semester = $request->integer('semester_id')
            ? Semester::find($request->integer('semester_id'))
            : Semester::current();

        return view('student.attendance.index', [
            'student' => $student,
            'semester' => $semester,
            'semesters' => Semester::orderByDesc('start_date')->get(),
            'stats' => $reports->studentOverall($student, $semester),
            'threshold' => (int) config('attendance.min_percentage'),
        ]);
    }

    public function show(ClassSection $section, AttendanceReportService $reports): View
    {
        $student = auth()->user()->student;

        // A student may only read a class they are actually enrolled in.
        abort_unless($student->isEnrolledIn($section), 403, 'You are not enrolled in this class.');

        $section->load(['course', 'semester', 'lecturer.user', 'schedules']);

        $records = $student->attendanceRecords()
            ->with('session')
            ->whereHas('session', fn ($q) => $q->where('class_section_id', $section->id))
            ->get()
            ->keyBy('attendance_session_id');

        return view('student.attendance.show', [
            'student' => $student,
            'section' => $section,
            'stats' => $reports->studentClassStats($student, $section),
            'threshold' => (int) config('attendance.min_percentage'),
            'sessions' => $section->sessions()
                ->closed()
                ->orderByDesc('session_date')
                ->orderByDesc('start_time')
                ->get(),
            'records' => $records,
        ]);
    }
}
