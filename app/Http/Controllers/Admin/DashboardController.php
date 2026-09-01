<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SessionStatus;
use App\Enums\StudentStatus;
use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\ClassSection;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Semester;
use App\Models\Student;
use App\Services\AttendanceReportService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(AttendanceReportService $reports): View
    {
        $semester = Semester::current();

        return view('admin.dashboard', [
            'semester' => $semester,
            'totals' => $reports->universityTotals($semester),
            'counts' => [
                'students' => Student::where('status', StudentStatus::Active)->count(),
                'lecturers' => Lecturer::count(),
                'courses' => Course::count(),
                'sections' => ClassSection::inSemester($semester)->count(),
            ],
            'atRisk' => $reports->lowAttendance($semester)->take(8),
            'todaySessions' => AttendanceSession::with(['classSection.course', 'classSection.lecturer.user'])
                ->withCount('records')
                ->onDate(now())
                ->orderBy('start_time')
                ->get(),
            'openSessions' => AttendanceSession::where('status', SessionStatus::Open)->count(),
        ]);
    }
}
