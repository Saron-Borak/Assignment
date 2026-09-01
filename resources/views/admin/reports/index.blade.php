@extends('layouts.app')
@section('title', 'Attendance overview')
@section('heading', 'Attendance overview')
@section('subheading', $semester ? $semester->name : 'All semesters')

@section('toolbar')
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm no-print"><i class="bi bi-printer me-1"></i>Print</button>
@endsection

@section('content')
<form method="GET" class="row g-2 align-items-center mb-3 no-print">
    <div class="col-sm-4 col-lg-3">
        <select name="semester_id" class="form-select form-select-sm">
            <option value="">All semesters</option>
            @foreach ($semesters as $option)
                <option value="{{ $option->id }}" @selected($semester?->id === $option->id)>{{ $option->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-4 col-lg-3">
        <select name="faculty_id" class="form-select form-select-sm">
            <option value="">All faculties</option>
            @foreach ($faculties as $faculty)
                <option value="{{ $faculty->id }}" @selected($facultyId === $faculty->id)>{{ $faculty->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-sm btn-primary">Apply</button></div>
</form>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <x-stat-card label="Overall attendance"
                     :value="number_format($totals['percentage'], 1).'%'"
                     icon="bi-graph-up-arrow"
                     :variant="$totals['percentage'] >= config('attendance.min_percentage') ? 'success' : 'danger'" />
    </div>
    <div class="col-6 col-lg-3">
        <x-stat-card label="Marks recorded" :value="number_format($totals['held'])" icon="bi-card-checklist" variant="primary" />
    </div>
    <div class="col-6 col-lg-3">
        <x-stat-card label="Absences" :value="number_format($totals['absent'])" icon="bi-x-circle" variant="danger" />
    </div>
    <div class="col-6 col-lg-3">
        <x-stat-card label="Late arrivals" :value="number_format($totals['late'])" icon="bi-clock" variant="warning" />
    </div>
</div>

<x-page-card title="By class section" subtitle="Cohort attendance for every section">
    @if ($sections->isEmpty())
        <x-empty-state icon="bi-bar-chart" title="No data yet"
                       message="Figures appear once sessions have been held and closed." />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Class</th><th>Lecturer</th>
                        <th class="text-end">Students</th><th class="text-end">Marks</th>
                        <th class="text-end">Present</th><th class="text-end">Late</th><th class="text-end">Absent</th>
                        <th style="width:170px">Attendance</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($sections as $row)
                    <tr class="{{ $row->at_risk ? 'row-at-risk' : '' }}">
                        <td>
                            <a href="{{ route('admin.reports.class-section', $row->class_section_id) }}" class="fw-semibold text-decoration-none">
                                {{ $row->course_code }}-{{ $row->section_code }}
                            </a>
                            <div class="small text-secondary">{{ $row->course_title }}</div>
                        </td>
                        <td class="small">{{ $row->lecturer_name }}</td>
                        <td class="text-end">{{ $row->students }}</td>
                        <td class="text-end">{{ $row->held }}</td>
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
@endsection
