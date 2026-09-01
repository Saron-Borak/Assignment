<?php

namespace App\Http\Controllers\Student;

use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\Semester;
use App\Services\AttendanceReportService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(AttendanceReportService $reports): View
    {
        $student = auth()->user()->student;
        $semester = Semester::current();

        $stats = $reports->studentOverall($student, $semester);
        $sectionIds = $student->enrollments()->active()->pluck('class_section_id');

        // Overall figure across every class, from the same counters the
        // per-class rows use.
        $held = $stats->sum('countable');
        $attended = $stats->sum('attended');

        return view('student.dashboard', [
            'student' => $student,
            'semester' => $semester,
            'stats' => $stats,
            'overall' => $held > 0 ? round($attended / $held * 100, 1) : 0.0,
            'atRisk' => $stats->filter(fn ($row) => $row->at_risk),
            'threshold' => (int) config('attendance.min_percentage'),
            'openNow' => AttendanceSession::with('classSection.course')
                ->whereIn('class_section_id', $sectionIds)
                ->where('status', SessionStatus::Open)
                ->whereDoesntHave('records', fn ($q) => $q->where('student_id', $student->id))
                ->get(),
            'recent' => $student->attendanceRecords()
                ->with('session.classSection.course')
                ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendance_records.attendance_session_id')
                ->orderByDesc('attendance_sessions.session_date')
                ->orderByDesc('attendance_sessions.start_time')
                ->select('attendance_records.*')
                ->limit(8)
                ->get(),
        ]);
    }
}
