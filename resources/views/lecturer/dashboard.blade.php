@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Good day, '.$lecturer->displayName())
@section('subheading', $semester ? $semester->name.' · '.now()->format('l, d F Y') : now()->format('l, d F Y'))

@section('content')
    @if ($openSessions->isNotEmpty())
        <div class="alert alert-success d-flex align-items-center flex-wrap gap-2">
            <i class="bi bi-broadcast fs-5"></i>
            <div class="me-auto">
                <strong>{{ $openSessions->count() }} session(s) currently open for check-in.</strong>
                <div class="small">Students can scan the QR code until you close the register.</div>
            </div>
            @foreach ($openSessions as $session)
                <a href="{{ route('lecturer.sessions.qr', $session) }}" class="btn btn-sm btn-success">
                    <i class="bi bi-qr-code me-1"></i>{{ $session->classSection->course->code }} code
                </a>
            @endforeach
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-card label="My classes" :value="$sections->count()" icon="bi-people" variant="primary" :href="route('lecturer.classes.index')" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card label="Students taught" :value="$sections->sum('students_count')" icon="bi-mortarboard" variant="info" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card label="Classes today" :value="$today->count()" icon="bi-calendar-day" variant="secondary" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card label="Open now" :value="$openSessions->count()" icon="bi-broadcast" variant="success" />
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <x-page-card title="Today's sessions" :subtitle="now()->format('l, d F Y')">
                @if ($today->isEmpty())
                    <x-empty-state icon="bi-calendar-x" title="Nothing scheduled today"
                                   message="Generate sessions from a class timetable to see them here.">
                        <a href="{{ route('lecturer.classes.index') }}" class="btn btn-sm btn-outline-secondary">Go to my classes</a>
                    </x-empty-state>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Time</th><th>Class</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                            <tbody>
                            @foreach ($today as $session)
                                <tr>
                                    <td class="text-nowrap small fw-semibold">{{ $session->timeRange() }}</td>
                                    <td>
                                        <a href="{{ route('lecturer.sessions.show', $session) }}" class="fw-semibold text-decoration-none">
                                            {{ $session->classSection->course->code }}-{{ $session->classSection->section_code }}
                                        </a>
                                        <div class="small text-secondary">{{ $session->records_count }} marked</div>
                                    </td>
                                    <td><x-status-badge :status="$session->status" /></td>
                                    <td class="text-end text-nowrap">
                                        @if ($session->status === \App\Enums\SessionStatus::Scheduled)
                                            <form method="POST" action="{{ route('lecturer.sessions.open', $session) }}" class="d-inline">
                                                @csrf @method('PUT')
                                                <button class="btn btn-sm btn-success"><i class="bi bi-play-fill me-1"></i>Open</button>
                                            </form>
                                        @elseif ($session->isOpen())
                                            <a href="{{ route('lecturer.sessions.qr', $session) }}" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-qr-code me-1"></i>Show code
                                            </a>
                                        @else
                                            <a href="{{ route('lecturer.sessions.show', $session) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-page-card>
        </div>

        <div class="col-lg-5">
            <x-page-card title="My classes" class="mb-3">
                @if ($sections->isEmpty())
                    <x-empty-state icon="bi-people" title="No classes assigned"
                                   message="An administrator assigns class sections to you." />
                @else
                    <div class="list-group list-group-flush">
                        @foreach ($sections->sortBy('course.code') as $section)
                            <a href="{{ route('lecturer.classes.show', $section) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold">{{ $section->course->code }}-{{ $section->section_code }}</div>
                                        <div class="small text-secondary text-truncate">{{ $section->course->title }}</div>
                                    </div>
                                    <span class="badge text-bg-light border">{{ $section->students_count }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </x-page-card>

            <x-page-card title="Coming up">
                @if ($upcoming->isEmpty())
                    <x-empty-state icon="bi-calendar3" title="No upcoming sessions" />
                @else
                    <div class="list-group list-group-flush">
                        @foreach ($upcoming as $session)
                            <div class="list-group-item d-flex align-items-center gap-2">
                                <div class="flex-grow-1">
                                    <div class="small fw-semibold">{{ $session->classSection->course->code }}-{{ $session->classSection->section_code }}</div>
                                    <div class="text-secondary" style="font-size:.78rem">
                                        {{ $session->session_date->format('D, d M') }} · {{ $session->timeRange() }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-page-card>
        </div>
    </div>
@endsection
