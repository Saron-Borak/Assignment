@extends('layouts.app')
@section('title', $section->label())
@section('heading', $section->fullLabel())
@section('subheading', $section->semester->name.($section->room ? ' · Room '.$section->room : ''))

@section('toolbar')
    <a href="{{ route('lecturer.sessions.create', $section) }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>New session
    </a>
    <a href="{{ route('lecturer.classes.report', $section) }}" class="btn btn-outline-secondary btn-sm ms-2">
        <i class="bi bi-bar-chart-line me-1"></i>Report
    </a>
@endsection

@section('content')
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <x-stat-card label="Students" :value="$stats->count()" icon="bi-people" variant="primary" />
    </div>
    <div class="col-6 col-lg-3">
        <x-stat-card label="Sessions" :value="$sessions->total()" icon="bi-calendar-check" variant="info" />
    </div>
    <div class="col-6 col-lg-3">
        <x-stat-card label="At risk" :value="$stats->where('at_risk', true)->count()" icon="bi-exclamation-triangle" variant="danger"
                     :hint="'Below '.config('attendance.min_percentage').'%'" />
    </div>
    <div class="col-6 col-lg-3">
        <x-stat-card label="Class average"
                     :value="number_format($stats->avg('percentage') ?? 0, 1).'%'"
                     icon="bi-graph-up" variant="secondary" />
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <x-page-card title="Sessions">
            <x-slot:actions>
                <a href="{{ route('lecturer.sessions.create', $section) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-calendar-plus me-1"></i>Add / generate
                </a>
            </x-slot:actions>

            @if ($sessions->isEmpty())
                <x-empty-state icon="bi-calendar-x" title="No sessions yet"
                               message="Generate a whole semester from the timetable, or add a one-off session.">
                    <a href="{{ route('lecturer.sessions.create', $section) }}" class="btn btn-sm btn-primary">Create sessions</a>
                </x-empty-state>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Date</th><th>Time</th><th>Status</th><th class="text-end">Marked</th><th class="text-end">Action</th></tr></thead>
                        <tbody>
                        @foreach ($sessions as $session)
                            <tr>
                                <td>
                                    <a href="{{ route('lecturer.sessions.show', $session) }}" class="fw-semibold text-decoration-none">
                                        {{ $session->session_date->format('D, d M Y') }}
                                    </a>
                                    @if ($session->topic)<div class="small text-secondary">{{ $session->topic }}</div>@endif
                                </td>
                                <td class="small text-nowrap">{{ $session->timeRange() }}</td>
                                <td><x-status-badge :status="$session->status" /></td>
                                <td class="text-end small">{{ $session->records_count }}</td>
                                <td class="text-end text-nowrap">
                                    @if ($session->status === \App\Enums\SessionStatus::Scheduled)
                                        <form method="POST" action="{{ route('lecturer.sessions.open', $session) }}" class="d-inline">
                                            @csrf @method('PUT')
                                            <button class="btn btn-sm btn-success" title="Open for check-in"><i class="bi bi-play-fill"></i></button>
                                        </form>
                                    @elseif ($session->isOpen())
                                        <a href="{{ route('lecturer.sessions.qr', $session) }}" class="btn btn-sm btn-outline-success" title="Show QR"><i class="bi bi-qr-code"></i></a>
                                    @endif
                                    <a href="{{ route('lecturer.sessions.mark', $session) }}" class="btn btn-sm btn-outline-secondary" title="Mark register"><i class="bi bi-pencil-square"></i></a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white">{{ $sessions->links() }}</div>
            @endif
        </x-page-card>
    </div>

    <div class="col-lg-5">
        <x-page-card title="Roster attendance">
            @if ($stats->isEmpty())
                <x-empty-state icon="bi-person-plus" title="Nobody enrolled"
                               message="An administrator manages the roster for this section." />
            @else
                <div class="table-responsive" style="max-height:520px; overflow-y:auto">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="sticky-top bg-white"><tr><th>Student</th><th style="width:150px">Attendance</th></tr></thead>
                        <tbody>
                        @foreach ($stats as $row)
                            <tr class="{{ $row->at_risk ? 'row-at-risk' : '' }}">
                                <td>
                                    <div class="fw-semibold small">{{ $row->name }}</div>
                                    <div class="text-secondary" style="font-size:.75rem">{{ $row->student_no }}</div>
                                </td>
                                <td><x-percentage-bar :percentage="$row->percentage" /></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-page-card>
    </div>
</div>
@endsection
