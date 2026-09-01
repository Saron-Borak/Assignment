@php($user = auth()->user())
<aside class="app-sidebar">
    <div class="d-flex align-items-center gap-2 px-3 py-3 border-bottom border-light border-opacity-10">
        <span class="brand-mark">{{ config('attendance.university_short_name') }}</span>
        <div class="lh-sm text-white">
            <div class="fw-semibold" style="font-size:.92rem">Attendance</div>
            <div class="text-white-50" style="font-size:.7rem">East Asia Mgmt University</div>
        </div>
    </div>

    <nav class="nav flex-column px-2 pb-4">
        @if($user->isAdmin())
            <div class="nav-section">Overview</div>
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>

            <div class="nav-section">Academic structure</div>
            <a class="nav-link {{ request()->routeIs('admin.faculties.*') ? 'active' : '' }}" href="{{ route('admin.faculties.index') }}">
                <i class="bi bi-building me-2"></i>Faculties
            </a>
            <a class="nav-link {{ request()->routeIs('admin.programs.*') ? 'active' : '' }}" href="{{ route('admin.programs.index') }}">
                <i class="bi bi-diagram-3 me-2"></i>Programs
            </a>
            <a class="nav-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}" href="{{ route('admin.courses.index') }}">
                <i class="bi bi-journal-bookmark me-2"></i>Courses
            </a>
            <a class="nav-link {{ request()->routeIs('admin.semesters.*') ? 'active' : '' }}" href="{{ route('admin.semesters.index') }}">
                <i class="bi bi-calendar3 me-2"></i>Semesters
            </a>

            <div class="nav-section">People</div>
            <a class="nav-link {{ request()->routeIs('admin.lecturers.*') ? 'active' : '' }}" href="{{ route('admin.lecturers.index') }}">
                <i class="bi bi-person-video3 me-2"></i>Lecturers
            </a>
            <a class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}" href="{{ route('admin.students.index') }}">
                <i class="bi bi-mortarboard me-2"></i>Students
            </a>
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                <i class="bi bi-shield-lock me-2"></i>User accounts
            </a>

            <div class="nav-section">Teaching</div>
            <a class="nav-link {{ request()->routeIs('admin.class-sections.*') ? 'active' : '' }}" href="{{ route('admin.class-sections.index') }}">
                <i class="bi bi-people me-2"></i>Class sections
            </a>

            <div class="nav-section">Reports</div>
            <a class="nav-link {{ request()->routeIs('admin.reports.index') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
                <i class="bi bi-bar-chart-line me-2"></i>Attendance overview
            </a>
            <a class="nav-link {{ request()->routeIs('admin.reports.low-attendance') ? 'active' : '' }}" href="{{ route('admin.reports.low-attendance') }}">
                <i class="bi bi-exclamation-triangle me-2"></i>At-risk students
            </a>

        @elseif($user->isLecturer())
            <div class="nav-section">Teaching</div>
            <a class="nav-link {{ request()->routeIs('lecturer.dashboard') ? 'active' : '' }}" href="{{ route('lecturer.dashboard') }}">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
            <a class="nav-link {{ request()->routeIs('lecturer.classes.*') ? 'active' : '' }}" href="{{ route('lecturer.classes.index') }}">
                <i class="bi bi-people me-2"></i>My classes
            </a>
            <a class="nav-link {{ request()->routeIs('lecturer.sessions.index') ? 'active' : '' }}" href="{{ route('lecturer.sessions.index') }}">
                <i class="bi bi-calendar-check me-2"></i>Class sessions
            </a>

        @else
            <div class="nav-section">My studies</div>
            <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
            <a class="nav-link {{ request()->routeIs('student.attendance.*') ? 'active' : '' }}" href="{{ route('student.attendance.index') }}">
                <i class="bi bi-list-check me-2"></i>My attendance
            </a>
            <a class="nav-link {{ request()->routeIs('student.check-in.*') ? 'active' : '' }}" href="{{ route('student.check-in.create') }}">
                <i class="bi bi-qr-code-scan me-2"></i>Check in
            </a>
        @endif

        <div class="nav-section">Account</div>
        <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
            <i class="bi bi-person-gear me-2"></i>My profile
        </a>
    </nav>
</aside>
