@extends('layouts.app')
@section('title', 'Check in')
@section('heading', 'Class check-in')
@section('subheading', 'Scan the projected QR code, or type the six-character code')

@section('content')
<div class="row g-3">
    <div class="col-lg-6">
        <x-page-card title="Enter the check-in code">
            <form method="POST" action="{{ route('student.check-in.store') }}">
                @csrf
                <div class="card-body">
                    <p class="text-secondary small">
                        Your lecturer displays a six-character code beside the QR code on screen.
                        The code changes every minute, so enter the one showing right now.
                    </p>

                    <label for="code" class="form-label">Check-in code</label>
                    <input type="text" id="code" name="code" maxlength="6" required autofocus
                           autocomplete="off" autocapitalize="characters" spellcheck="false"
                           class="form-control form-control-lg text-center text-uppercase font-monospace @error('code') is-invalid @enderror"
                           style="letter-spacing:.5rem; font-size:1.8rem"
                           value="{{ old('code') }}" placeholder="ABC123">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="card-footer bg-white">
                    <button class="btn btn-primary w-100 py-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Check in
                    </button>
                </div>
            </form>
        </x-page-card>

        <div class="alert alert-light border mt-3 small">
            <div class="fw-semibold mb-1"><i class="bi bi-info-circle me-1"></i>How check-in works</div>
            <ul class="mb-0 ps-3">
                <li>You can only check in while your lecturer has the session open.</li>
                <li>You must be enrolled in the class.</li>
                <li>Checking in after {{ config('attendance.late_after_minutes') }} minutes past the start time is recorded as <strong>late</strong>.</li>
                <li>You can only check in once per session.</li>
            </ul>
        </div>
    </div>

    <div class="col-lg-6">
        <x-page-card title="My recent self check-ins">
            @if ($recent->isEmpty())
                <x-empty-state icon="bi-qr-code-scan" title="No self check-ins yet"
                               message="Scan the code your lecturer projects at the start of class." />
            @else
                <div class="list-group list-group-flush">
                    @foreach ($recent as $record)
                        <div class="list-group-item d-flex align-items-center gap-2">
                            <div class="flex-grow-1 min-w-0">
                                <div class="small fw-semibold">
                                    {{ $record->session->classSection->course->code }}-{{ $record->session->classSection->section_code }}
                                </div>
                                <div class="text-secondary" style="font-size:.78rem">
                                    {{ $record->session->session_date->format('D, d M Y') }}
                                    · {{ $record->marked_at?->format('H:i') }}
                                    · {{ $record->marked_via->label() }}
                                </div>
                            </div>
                            <x-status-badge :status="$record->status" />
                        </div>
                    @endforeach
                </div>
            @endif
        </x-page-card>
    </div>
</div>
@endsection
