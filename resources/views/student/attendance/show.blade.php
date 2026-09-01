@extends('layouts.app')
@section('title', $section->label())
@section('heading', $section->fullLabel())
@section('subheading', $section->semester->name.' · '.$section->lecturer->displayName())

@section('toolbar')
    <a href="{{ route('student.attendance.index') }}" class="btn btn-outline-secondary btn-sm no-print">
        <i class="bi bi-arrow-left me-1"></i>All classes
    </a>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm ms-2 no-print"><i class="bi bi-printer me-1"></i>Print</button>
@endsection

@section('content')
@if ($stats['at_risk'])
    <div class="alert alert-danger d-flex align-items-start gap-2">
        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
        <div>
            <strong>Your attendance in this class is {{ number_format($stats['percentage'], 1) }}%.</strong>
            <div class="small">
                The university requires at least {{ $threshold }}%. Speak to
                {{ $section->lecturer->displayName() }} or your faculty office.
            </div>
        </div>
    </div>
@endif

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <x-stat-card label="Attendance" :value="number_format($stats['percentage'], 1).'%'" icon="bi-graph-up-arrow"
                     :variant="$stats['at_risk'] ? 'danger' : 'success'" :hint="'Requirement: '.$threshold.'%'" />
    </div>
    <div class="col-6 col-lg-3">
        <x-stat-card label="Present" :value="$stats['present']" icon="bi-check-circle" variant="success" />
    </div>
    <div class="col-6 col-lg-3">
        <x-stat-card label="Late" :value="$stats['late']" icon="bi-clock" variant="warning" />
    </div>
    <div class="col-6 col-lg-3">
        <x-stat-card label="Absent" :value="$stats['absent']" icon="bi-x-circle" variant="danger" />
    </div>
</div>

<x-page-card title="Session history" subtitle="Only completed sessions count towards your percentage">
    @if ($sessions->isEmpty())
        <x-empty-state icon="bi-calendar-x" title="No completed sessions yet"
                       message="Your attendance appears here after each class is closed by the lecturer." />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Date</th><th>Time</th><th>Topic</th><th>Status</th><th>Recorded</th><th>Remark</th></tr></thead>
                <tbody>
                @foreach ($sessions as $session)
                    @php($record = $records->get($session->id))
                    <tr>
                        <td class="fw-semibold">{{ $session->session_date->format('D, d M Y') }}</td>
                        <td class="small text-nowrap">{{ $session->timeRange() }}</td>
                        <td class="small text-secondary">{{ $session->topic ?: '-' }}</td>
                        <td>
                            @if ($record)
                                <x-status-badge :status="$record->status" />
                            @else
                                <span class="badge text-bg-light border text-secondary">Not recorded</span>
                            @endif
                        </td>
                        <td class="small text-secondary text-nowrap">
                            {{ $record?->marked_at?->format('H:i') ?: '-' }}
                            @if ($record?->marked_via)
                                <div style="font-size:.72rem">{{ $record->marked_via->label() }}</div>
                            @endif
                        </td>
                        <td class="small">{{ $record?->remarks ?: '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-page-card>
@endsection
