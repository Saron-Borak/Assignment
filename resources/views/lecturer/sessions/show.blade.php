@extends('layouts.app')
@section('title', 'Session')
@section('heading', $session->classSection->fullLabel())
@section('subheading', $session->session_date->format('l, d F Y').' · '.$session->timeRange())

@section('toolbar')
    <a href="{{ route('lecturer.sessions.mark', $session) }}" class="btn btn-primary btn-sm no-print">
        <i class="bi bi-pencil-square me-1"></i>Mark register
    </a>
    @if ($session->isOpen())
        <a href="{{ route('lecturer.sessions.qr', $session) }}" class="btn btn-success btn-sm ms-2 no-print">
            <i class="bi bi-qr-code me-1"></i>Show code
        </a>
    @endif
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm ms-2 no-print"><i class="bi bi-printer me-1"></i>Print</button>
@endsection

@section('content')
@php
    $present = $records->where('status', \App\Enums\AttendanceStatus::Present)->count();
    $late = $records->where('status', \App\Enums\AttendanceStatus::Late)->count();
    $absent = $records->where('status', \App\Enums\AttendanceStatus::Absent)->count();
    $excused = $records->where('status', \App\Enums\AttendanceStatus::Excused)->count();
@endphp

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3"><x-stat-card label="Present" :value="$present" icon="bi-check-circle" variant="success" /></div>
    <div class="col-6 col-lg-3"><x-stat-card label="Late" :value="$late" icon="bi-clock" variant="warning" /></div>
    <div class="col-6 col-lg-3"><x-stat-card label="Absent" :value="$absent" icon="bi-x-circle" variant="danger" /></div>
    <div class="col-6 col-lg-3"><x-stat-card label="Excused" :value="$excused" icon="bi-info-circle" variant="secondary" /></div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <x-page-card title="Attendance record">
            @if ($records->isEmpty())
                <x-empty-state icon="bi-clipboard-x" title="Nothing recorded yet"
                               message="Open the session for self check-in, or mark the register by hand.">
                    <a href="{{ route('lecturer.sessions.mark', $session) }}" class="btn btn-sm btn-primary">Mark register</a>
                </x-empty-state>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Student</th><th>Status</th><th>Recorded</th><th>How</th><th>Remark</th></tr></thead>
                        <tbody>
                        @foreach ($records as $record)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $record->student->user->name }}</div>
                                    <div class="small text-secondary">{{ $record->student->student_no }}</div>
                                </td>
                                <td><x-status-badge :status="$record->status" /></td>
                                <td class="small text-secondary text-nowrap">{{ $record->marked_at?->format('H:i:s') ?: '-' }}</td>
                                <td class="small text-secondary">{{ $record->marked_via->label() }}</td>
                                <td class="small">{{ $record->remarks ?: '-' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-page-card>
    </div>

    <div class="col-lg-4">
        <x-page-card title="Session details">
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-secondary fw-normal">Status</dt>
                    <dd class="col-7"><x-status-badge :status="$session->status" /></dd>
                    <dt class="col-5 text-secondary fw-normal">Topic</dt><dd class="col-7">{{ $session->topic ?: '-' }}</dd>
                    <dt class="col-5 text-secondary fw-normal">Late after</dt><dd class="col-7">{{ $session->lateThreshold()->format('H:i') }}</dd>
                    <dt class="col-5 text-secondary fw-normal">Opened</dt><dd class="col-7">{{ $session->opened_at?->format('d M, H:i') ?: '-' }}</dd>
                    <dt class="col-5 text-secondary fw-normal">Closed</dt><dd class="col-7">{{ $session->closed_at?->format('d M, H:i') ?: '-' }}</dd>
                </dl>
            </div>
            <div class="card-footer bg-white d-grid gap-2 no-print">
                @if ($session->status === \App\Enums\SessionStatus::Scheduled)
                    <form method="POST" action="{{ route('lecturer.sessions.open', $session) }}">
                        @csrf @method('PUT')
                        <button class="btn btn-success w-100"><i class="bi bi-play-fill me-1"></i>Open for check-in</button>
                    </form>
                @elseif ($session->isOpen())
                    <form method="POST" action="{{ route('lecturer.sessions.close', $session) }}"
                          onsubmit="return confirm('Close this session? Anyone unmarked will be recorded absent.')">
                        @csrf @method('PUT')
                        <button class="btn btn-outline-danger w-100"><i class="bi bi-lock me-1"></i>Close session</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('lecturer.sessions.open', $session) }}"
                          onsubmit="return confirm('Reopen this session for check-in?')">
                        @csrf @method('PUT')
                        <button class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-clockwise me-1"></i>Reopen session</button>
                    </form>
                @endif

                @unless ($records->count())
                    <form method="POST" action="{{ route('lecturer.sessions.destroy', $session) }}"
                          onsubmit="return confirm('Delete this session?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-trash me-1"></i>Delete session</button>
                    </form>
                @endunless
            </div>
        </x-page-card>
    </div>
</div>
@endsection
