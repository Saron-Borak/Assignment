@extends('layouts.app')
@section('title', 'My classes')
@section('heading', 'My classes')
@section('subheading', $semester ? $semester->name : 'All semesters')

@section('content')
<form method="GET" class="row g-2 align-items-center mb-3">
    <div class="col-sm-4 col-lg-3">
        <select name="semester_id" class="form-select form-select-sm">
            <option value="">All semesters</option>
            @foreach ($semesters as $option)
                <option value="{{ $option->id }}" @selected($semester?->id === $option->id)>{{ $option->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-sm btn-primary">Apply</button></div>
</form>

@if ($sections->isEmpty())
    <x-page-card>
        <x-empty-state icon="bi-people" title="No classes assigned"
                       message="An administrator assigns class sections to you for each semester." />
    </x-page-card>
@else
    <div class="row g-3">
        @foreach ($sections as $section)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <div class="flex-grow-1">
                                <h2 class="h6 mb-1">
                                    <a href="{{ route('lecturer.classes.show', $section) }}" class="text-decoration-none stretched-link">
                                        {{ $section->course->code }}-{{ $section->section_code }}
                                    </a>
                                </h2>
                                <div class="small text-secondary">{{ $section->course->title }}</div>
                            </div>
                            <span class="badge text-bg-light border">{{ $section->students_count }} students</span>
                        </div>

                        <div class="small text-secondary mb-2">
                            @forelse ($section->schedules as $schedule)
                                <div><i class="bi bi-clock me-1"></i>{{ $schedule->dayName() }} {{ $schedule->timeRange() }}</div>
                            @empty
                                <div class="text-body-tertiary"><i class="bi bi-clock me-1"></i>Not timetabled</div>
                            @endforelse
                            @if ($section->room)
                                <div><i class="bi bi-geo-alt me-1"></i>{{ $section->room }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between small text-secondary">
                        <span>{{ $section->sessions_count }} session(s)</span>
                        <span>{{ $section->closed_sessions_count }} completed</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
