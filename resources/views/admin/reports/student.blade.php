@extends('layouts.app')
@section('title', 'Student report')
@section('heading', 'Report · '.$student->user->name)
@section('subheading', $student->student_no.' · '.$student->program->name)

@section('toolbar')
    <a href="{{ route('admin.students.show', $student) }}" class="btn btn-outline-secondary btn-sm no-print">
        <i class="bi bi-person me-1"></i>Student record
    </a>
    <a href="{{ route('admin.reports.student.export', $student) }}" class="btn btn-outline-success btn-sm ms-2 no-print">
        <i class="bi bi-filetype-csv me-1"></i>Download CSV
    </a>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm ms-2 no-print"><i class="bi bi-printer me-1"></i>Print</button>
@endsection

@section('content')
@php
    $countable = $stats->sum('countable');
    $attended = $stats->sum('attended');
    $overall = $countable > 0 ? round($attended / $countable * 100, 1) : 0.0;
@endphp

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <x-stat-card label="Overall attendance" :value="number_format($overall, 1).'%'" icon="bi-graph-up-arrow"
                     :variant="$overall >= config('attendance.min_percentage') ? 'success' : 'danger'" />
    </div>
    <div class="col-6 col-lg-3">
        <x-stat-card label="Classes enrolled" :value="$stats->count()" icon="bi-journal-bookmark" variant="primary" />
    </div>
    <div class="col-6 col-lg-3">
        <x-stat-card label="Sessions attended" :value="$attended.' / '.$countable" icon="bi-check-circle" variant="info" />
    </div>
    <div class="col-6 col-lg-3">
        <x-stat-card label="Classes at risk" :value="$stats->where('at_risk', true)->count()" icon="bi-exclamation-triangle" variant="danger" />
    </div>
</div>

@include('admin.students._attendance-table', ['stats' => $stats])
@endsection
