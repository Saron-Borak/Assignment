@extends('layouts.app')
@section('title', $lecturer->displayName())
@section('heading', $lecturer->displayName())
@section('subheading', $lecturer->staff_no.' · '.$lecturer->faculty->name)

@section('toolbar')
    <a href="{{ route('admin.lecturers.edit', $lecturer) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <x-page-card title="Contact">
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-secondary fw-normal">Email</dt><dd class="col-7">{{ $lecturer->user->email }}</dd>
                    <dt class="col-5 text-secondary fw-normal">Phone</dt><dd class="col-7">{{ $lecturer->user->phone ?: '—' }}</dd>
                    <dt class="col-5 text-secondary fw-normal">Faculty</dt><dd class="col-7">{{ $lecturer->faculty->name }}</dd>
                    <dt class="col-5 text-secondary fw-normal">Classes</dt><dd class="col-7">{{ $lecturer->class_sections_count }}</dd>
                    <dt class="col-5 text-secondary fw-normal">Account</dt>
                    <dd class="col-7">{{ $lecturer->user->is_active ? 'Active' : 'Disabled' }}</dd>
                </dl>
            </div>
        </x-page-card>
    </div>

    <div class="col-lg-8">
        <x-page-card title="Assigned class sections">
            @if ($lecturer->classSections->isEmpty())
                <x-empty-state icon="bi-people" title="No classes assigned"
                               message="Assign this lecturer to a section from the class sections screen." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Class</th><th>Semester</th><th>Timetable</th></tr></thead>
                        <tbody>
                        @foreach ($lecturer->classSections->sortBy('course.code') as $section)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.class-sections.show', $section) }}" class="fw-semibold text-decoration-none">
                                        {{ $section->course->code }}-{{ $section->section_code }}
                                    </a>
                                    <div class="small text-secondary">{{ $section->course->title }}</div>
                                </td>
                                <td class="small">{{ $section->semester->name }}</td>
                                <td class="small text-secondary">
                                    @forelse ($section->schedules as $schedule)
                                        <div>{{ $schedule->shortDayName() }} {{ $schedule->timeRange() }}</div>
                                    @empty
                                        <span class="text-body-tertiary">Not timetabled</span>
                                    @endforelse
                                </td>
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
