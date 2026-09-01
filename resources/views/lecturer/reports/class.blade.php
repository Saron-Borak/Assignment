@extends('layouts.app')
@section('title', 'Class report')
@section('heading', 'Report · '.$section->fullLabel())
@section('subheading', $section->semester->name)

@section('toolbar')
    <a href="{{ route('lecturer.classes.report.export', [$section, ...request()->query()]) }}"
       class="btn btn-outline-success btn-sm no-print">
        <i class="bi bi-filetype-csv me-1"></i>Download CSV
    </a>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm ms-2 no-print"><i class="bi bi-printer me-1"></i>Print</button>
@endsection

@section('content')
<form method="GET" class="row g-2 align-items-end mb-3 no-print">
    <div class="col-auto">
        <label for="from" class="form-label small mb-1">From</label>
        <input type="date" id="from" name="from" value="{{ $from }}" class="form-control form-control-sm">
    </div>
    <div class="col-auto">
        <label for="to" class="form-label small mb-1">To</label>
        <input type="date" id="to" name="to" value="{{ $to }}" class="form-control form-control-sm">
    </div>
    <div class="col-auto"><button class="btn btn-sm btn-primary">Apply</button></div>
    @if ($from || $to)
        <div class="col-auto"><a href="{{ route('lecturer.classes.report', $section) }}" class="btn btn-sm btn-outline-secondary">Clear</a></div>
    @endif
    <div class="col-auto ms-auto small text-secondary">{{ $sessions->count() }} closed session(s) in range</div>
</form>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <x-stat-card label="Students" :value="$stats->count()" icon="bi-people" variant="primary" />
    </div>
    <div class="col-6 col-lg-3">
        <x-stat-card label="Class average" :value="number_format($stats->avg('percentage') ?? 0, 1).'%'" icon="bi-graph-up" variant="info" />
    </div>
    <div class="col-6 col-lg-3">
        <x-stat-card label="At risk" :value="$stats->where('at_risk', true)->count()" icon="bi-exclamation-triangle" variant="danger" />
    </div>
    <div class="col-6 col-lg-3">
        <x-stat-card label="Sessions held" :value="$sessions->count()" icon="bi-calendar-check" variant="secondary" />
    </div>
</div>

<x-page-card title="Attendance register" :subtitle="'Minimum required: '.config('attendance.min_percentage').'%'">
    @if ($stats->isEmpty())
        <x-empty-state icon="bi-people" title="Nobody enrolled" message="An administrator manages this roster." />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Student</th><th class="text-end">Held</th>
                        <th class="text-end">Present</th><th class="text-end">Late</th>
                        <th class="text-end">Absent</th><th class="text-end">Excused</th>
                        <th style="width:170px">Attendance</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($stats as $row)
                    <tr class="{{ $row->at_risk ? 'row-at-risk' : '' }}">
                        <td>
                            <div class="fw-semibold">{{ $row->name }}</div>
                            <div class="small text-secondary">{{ $row->student_no }}</div>
                        </td>
                        <td class="text-end">{{ $row->held }}</td>
                        <td class="text-end text-success">{{ $row->present }}</td>
                        <td class="text-end text-warning-emphasis">{{ $row->late }}</td>
                        <td class="text-end text-danger">{{ $row->absent }}</td>
                        <td class="text-end text-secondary">{{ $row->excused }}</td>
                        <td><x-percentage-bar :percentage="$row->percentage" /></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-page-card>
@endsection
