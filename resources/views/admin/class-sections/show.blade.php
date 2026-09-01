@extends('layouts.app')
@section('title', $section->label())
@section('heading', $section->fullLabel())
@section('subheading', $section->semester->name.' · '.$section->lecturer->displayName())

@section('toolbar')
    <a href="{{ route('admin.class-sections.enrollments.edit', $section) }}" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus me-1"></i>Manage roster
    </a>
    <a href="{{ route('admin.reports.class-section', $section) }}" class="btn btn-outline-secondary btn-sm ms-2">
        <i class="bi bi-bar-chart-line me-1"></i>Report
    </a>
    <a href="{{ route('admin.class-sections.edit', $section) }}" class="btn btn-outline-secondary btn-sm ms-2">
        <i class="bi bi-pencil me-1"></i>Edit
    </a>
@endsection

@section('content')
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <x-stat-card label="Enrolled" :value="$stats->count().' / '.$section->capacity" icon="bi-people" variant="primary" />
    </div>
    <div class="col-6 col-lg-3">
        <x-stat-card label="Sessions" :value="$sessionCount" icon="bi-calendar-check" variant="info" />
    </div>
    <div class="col-6 col-lg-3">
        <x-stat-card label="At risk" :value="$stats->where('at_risk', true)->count()" icon="bi-exclamation-triangle" variant="danger"
                     :hint="'Below '.config('attendance.min_percentage').'%'" />
    </div>
    <div class="col-6 col-lg-3">
        <x-stat-card label="Room" :value="$section->room ?: 'TBC'" icon="bi-geo-alt" variant="secondary" />
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <x-page-card title="Roster and attendance">
            @if ($stats->isEmpty())
                <x-empty-state icon="bi-person-plus" title="Nobody enrolled yet" message="Add students to this section to start taking attendance.">
                    <a href="{{ route('admin.class-sections.enrollments.edit', $section) }}" class="btn btn-sm btn-primary">Manage roster</a>
                </x-empty-state>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Student</th><th class="text-end">Present</th><th class="text-end">Late</th>
                                <th class="text-end">Absent</th><th style="width:160px">Attendance</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($stats as $row)
                            <tr class="{{ $row->at_risk ? 'row-at-risk' : '' }}">
                                <td>
                                    <a href="{{ route('admin.reports.student', $row->student_id) }}" class="fw-semibold text-decoration-none">{{ $row->name }}</a>
                                    <div class="small text-secondary">{{ $row->student_no }}</div>
                                </td>
                                <td class="text-end text-success">{{ $row->present }}</td>
                                <td class="text-end text-warning-emphasis">{{ $row->late }}</td>
                                <td class="text-end text-danger">{{ $row->absent }}</td>
                                <td><x-percentage-bar :percentage="$row->percentage" /></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-page-card>
    </div>

    <div class="col-lg-4">
        <x-page-card title="Timetable" class="mb-3">
            <div class="card-body">
                @forelse ($section->schedules as $schedule)
                    <div class="d-flex justify-content-between border-bottom py-2 small">
                        <span class="fw-semibold">{{ $schedule->dayName() }}</span>
                        <span class="text-secondary">{{ $schedule->timeRange() }}</span>
                    </div>
                @empty
                    <p class="small text-secondary mb-0">No timetable set. Lecturers cannot bulk-generate sessions until one exists.</p>
                @endforelse
            </div>
        </x-page-card>

        <x-page-card title="Recent sessions">
            @if ($sessions->isEmpty())
                <x-empty-state icon="bi-calendar-x" title="No sessions yet"
                               message="The lecturer creates sessions from the timetable." />
            @else
                <div class="list-group list-group-flush">
                    @foreach ($sessions as $session)
                        <div class="list-group-item d-flex align-items-center gap-2">
                            <div class="flex-grow-1">
                                <div class="small fw-semibold">{{ $session->session_date->format('D, d M Y') }}</div>
                                <div class="text-secondary" style="font-size:.78rem">{{ $session->timeRange() }} · {{ $session->records_count }} marked</div>
                            </div>
                            <x-status-badge :status="$session->status" />
                        </div>
                    @endforeach
                </div>
            @endif
        </x-page-card>
    </div>
</div>
@endsection
