@extends('layouts.app')
@section('title', 'Class sections')
@section('heading', 'Class sections')
@section('subheading', 'Course offerings for a semester, each with its own roster')

@section('toolbar')
    <a href="{{ route('admin.class-sections.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New section</a>
@endsection

@section('content')
<x-page-card>
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-sm-6 col-lg-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search course">
                </div>
            </div>
            <div class="col-sm-4 col-lg-3">
                <select name="semester_id" class="form-select form-select-sm">
                    <option value="">All semesters</option>
                    @foreach ($semesters as $semester)
                        <option value="{{ $semester->id }}" @selected(request('semester_id') == $semester->id)>{{ $semester->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4 col-lg-3">
                <select name="lecturer_id" class="form-select form-select-sm">
                    <option value="">All lecturers</option>
                    @foreach ($lecturers as $lecturer)
                        <option value="{{ $lecturer->id }}" @selected(request('lecturer_id') == $lecturer->id)>{{ $lecturer->user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-outline-secondary">Filter</button></div>
        </form>
    </div>

    @if ($sections->isEmpty())
        <x-empty-state icon="bi-people" title="No class sections found" message="A section links a course, a semester and a lecturer.">
            <a href="{{ route('admin.class-sections.create') }}" class="btn btn-sm btn-primary">Create a section</a>
        </x-empty-state>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Class</th><th>Semester</th><th>Lecturer</th><th>Timetable</th>
                        <th class="text-end">Roster</th><th class="text-end">Sessions</th><th style="width:1%"></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($sections as $section)
                    <tr>
                        <td>
                            <a href="{{ route('admin.class-sections.show', $section) }}" class="fw-semibold text-decoration-none">
                                {{ $section->course->code }}-{{ $section->section_code }}
                            </a>
                            <div class="small text-secondary">{{ $section->course->title }}</div>
                        </td>
                        <td class="small">{{ $section->semester->name }}</td>
                        <td class="small">{{ $section->lecturer->user->name }}</td>
                        <td class="small text-secondary">
                            @forelse ($section->schedules as $schedule)
                                <div class="text-nowrap">{{ $schedule->shortDayName() }} {{ $schedule->timeRange() }}</div>
                            @empty
                                <span class="text-body-tertiary">Not timetabled</span>
                            @endforelse
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.class-sections.enrollments.edit', $section) }}" class="text-decoration-none">
                                {{ $section->students_count }}/{{ $section->capacity }}
                            </a>
                        </td>
                        <td class="text-end">{{ $section->sessions_count }}</td>
                        <td class="text-nowrap text-end">
                            <a href="{{ route('admin.class-sections.edit', $section) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.class-sections.destroy', $section) }}" class="d-inline"
                                  onsubmit="return confirm('Delete this class section?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $sections->links() }}</div>
    @endif
</x-page-card>
@endsection
