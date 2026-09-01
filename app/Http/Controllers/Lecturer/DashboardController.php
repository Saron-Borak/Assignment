<?php

namespace App\Http\Controllers\Lecturer;

use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\ClassSection;
use App\Models\Semester;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $lecturer = auth()->user()->lecturer;
        $semester = Semester::current();

        $sections = ClassSection::with(['course', 'semester', 'schedules'])
            ->withCount(['enrollments as students_count' => fn ($q) => $q->active()])
            ->forLecturer($lecturer)
            ->inSemester($semester)
            ->get();

        $sectionIds = $sections->pluck('id');

        return view('lecturer.dashboard', [
            'lecturer' => $lecturer,
            'semester' => $semester,
            'sections' => $sections,
            'today' => AttendanceSession::with('classSection.course')
                ->withCount('records')
                ->whereIn('class_section_id', $sectionIds)
                ->onDate(now())
                ->orderBy('start_time')
                ->get(),
            'openSessions' => AttendanceSession::with('classSection.course')
                ->withCount('records')
                ->whereIn('class_section_id', $sectionIds)
                ->where('status', SessionStatus::Open)
                ->get(),
            'upcoming' => AttendanceSession::with('classSection.course')
                ->whereIn('class_section_id', $sectionIds)
                ->where('status', SessionStatus::Scheduled)
                ->whereDate('session_date', '>', now())
                ->orderBy('session_date')
                ->orderBy('start_time')
                ->limit(6)
                ->get(),
        ]);
    }
}
