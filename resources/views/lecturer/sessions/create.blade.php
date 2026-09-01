@extends('layouts.app')
@section('title', 'New sessions')
@section('heading', 'Sessions · '.$section->fullLabel())
@section('subheading', $section->semester->name)

@section('content')
<div class="row g-3">
    <div class="col-lg-6">
        <x-page-card title="Generate from timetable"
                     subtitle="Creates one scheduled session per timetabled slot in the range">
            <div class="card-body">
                @if ($section->schedules->isEmpty())
                    <div class="alert alert-warning small mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        This section has no timetable yet, so sessions cannot be generated.
                        Ask an administrator to add one, or create a one-off session instead.
                    </div>
                @else
                    <div class="mb-3">
                        <div class="small text-secondary mb-1">Timetabled slots</div>
                        @foreach ($section->schedules as $schedule)
                            <span class="badge text-bg-light border me-1">{{ $schedule->shortDayName() }} {{ $schedule->timeRange() }}</span>
                        @endforeach
                    </div>

                    <form method="POST" action="{{ route('lecturer.sessions.generate', $section) }}">
                        @csrf
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <label for="from" class="form-label">From <span class="text-danger">*</span></label>
                                <input type="date" id="from" name="from" class="form-control @error('from') is-invalid @enderror"
                                       value="{{ old('from', $section->semester->start_date->format('Y-m-d')) }}" required>
                                @error('from')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label for="to" class="form-label">To <span class="text-danger">*</span></label>
                                <input type="date" id="to" name="to" class="form-control @error('to') is-invalid @enderror"
                                       value="{{ old('to', $section->semester->end_date->format('Y-m-d')) }}" required>
                                @error('to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="form-text mb-3">Existing sessions are left untouched, so this is safe to run more than once.</div>
                        <button class="btn btn-primary"><i class="bi bi-magic me-1"></i>Generate sessions</button>
                    </form>
                @endif
            </div>
        </x-page-card>
    </div>

    <div class="col-lg-6">
        <x-page-card title="Add a single session" subtitle="For a make-up class or a one-off meeting">
            <form method="POST" action="{{ route('lecturer.sessions.store', $section) }}">
                @csrf
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="session_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" id="session_date" name="session_date"
                                   class="form-control @error('session_date') is-invalid @enderror"
                                   value="{{ old('session_date', now()->toDateString()) }}" required>
                            @error('session_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label for="start_time" class="form-label">Start <span class="text-danger">*</span></label>
                            <input type="time" id="start_time" name="start_time"
                                   class="form-control @error('start_time') is-invalid @enderror"
                                   value="{{ old('start_time', '08:00') }}" required>
                            @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label for="end_time" class="form-label">End <span class="text-danger">*</span></label>
                            <input type="time" id="end_time" name="end_time"
                                   class="form-control @error('end_time') is-invalid @enderror"
                                   value="{{ old('end_time', '10:00') }}" required>
                            @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="topic" class="form-label">Topic</label>
                            <input type="text" id="topic" name="topic" class="form-control @error('topic') is-invalid @enderror"
                                   value="{{ old('topic') }}" placeholder="Optional lecture topic">
                            @error('topic')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex gap-2">
                    <button class="btn btn-outline-primary"><i class="bi bi-plus-lg me-1"></i>Create session</button>
                    <a href="{{ route('lecturer.classes.show', $section) }}" class="btn btn-outline-secondary">Back to class</a>
                </div>
            </form>
        </x-page-card>
    </div>
</div>
@endsection
