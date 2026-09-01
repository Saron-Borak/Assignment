@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Administrator dashboard')
@section('subheading', $semester ? $semester->name.' · '.$semester->code : 'No active semester set')

@section('content')
    @unless($semester)
        <div class="alert alert-warning d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>No semester is marked active. <a href="{{ route('admin.semesters.index') }}" class="alert-link">Activate one</a> so dashboards and reports have a default period.</div>
        </div>
    @endunless

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <x-stat-card label="Active students" :value="number_format($counts['students'])" icon="bi-mortarboard" variant="primary" :href="route('admin.students.index')" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Lecturers" :value="number_format($counts['lecturers'])" icon="bi-person-video3" variant="info" :href="route('admin.lecturers.index')" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Class sections" :value="number_format($counts['sections'])" icon="bi-people" variant="secondary" :href="route('admin.class-sections.index')" hint="This semester" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card
                label="Attendance rate"
                :value="number_format($totals['percentage'], 1).'%'"
                icon="bi-graph-up-arrow"
                :variant="$totals['percentage'] >= config('attendance.min_percentage') ? 'success' : 'danger'"
                :hint="number_format($totals['attended']).' of '.number_format($totals['countable']).' marks'" />
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <x-page-card title="Today's classes" :subtitle="now()->format('l, d F Y')">
                @if ($todaySessions->isEmpty())
                    <x-empty-state icon="bi-calendar-x" title="No classes scheduled today"
                                   message="Sessions appear here once a lecturer generates them from the timetable." />
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Time</th><th>Class</th><th>Lecturer</th><th>Status</th><th class="text-end">Marked</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($todaySessions as $session)
                                    <tr>
                                        <td class="text-nowrap small">{{ $session->timeRange() }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $session->classSection->course->code }}-{{ $session->classSection->section_code }}</div>
                                            <div class="small text-secondary">{{ $session->classSection->course->title }}</div>
                                        </td>
                                        <td class="small">{{ $session->classSection->lecturer->user->name }}</td>
                                        <td><x-status-badge :status="$session->status" /></td>
                                        <td class="text-end small">{{ $session->records_count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-page-card>
        </div>

        <div class="col-lg-5">
            <x-page-card :title="'Students below '.config('attendance.min_percentage').'%'" subtitle="Worst attendance first">
                <x-slot:actions>
                    <a href="{{ route('admin.reports.low-attendance') }}" class="btn btn-sm btn-outline-secondary">View all</a>
                </x-slot:actions>

                @if ($atRisk->isEmpty())
                    <x-empty-state icon="bi-emoji-smile" title="Nobody is at risk"
                                   message="Every student is meeting the minimum attendance requirement." />
                @else
                    <div class="list-group list-group-flush">
                        @foreach ($atRisk as $row)
                            <a href="{{ route('admin.reports.student', $row->student_id) }}"
                               class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-truncate">{{ $row->name }}</div>
                                    <div class="small text-secondary">{{ $row->student_no }} · {{ $row->course_code }}-{{ $row->section_code }}</div>
                                </div>
                                <div style="width:130px">
                                    <x-percentage-bar :percentage="$row->percentage" />
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </x-page-card>
        </div>
    </div>
@endsection
