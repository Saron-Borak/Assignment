@extends('layouts.app')
@section('title', 'Courses')
@section('heading', 'Courses')
@section('subheading', 'The course catalogue offered by the university')

@section('toolbar')
    <a href="{{ route('admin.courses.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New course</a>
@endsection

@section('content')
<x-page-card>
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-sm-6 col-lg-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search code or title">
                </div>
            </div>
            <div class="col-sm-4 col-lg-3">
                <select name="faculty_id" class="form-select form-select-sm">
                    <option value="">All faculties</option>
                    @foreach ($faculties as $faculty)
                        <option value="{{ $faculty->id }}" @selected(request('faculty_id') == $faculty->id)>{{ $faculty->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-outline-secondary">Filter</button></div>
        </form>
    </div>

    @if ($courses->isEmpty())
        <x-empty-state icon="bi-journal-bookmark" title="No courses found" message="Adjust your filters or add a new course.">
            <a href="{{ route('admin.courses.create') }}" class="btn btn-sm btn-primary">Create a course</a>
        </x-empty-state>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Code</th><th>Title</th><th>Faculty</th><th class="text-end">Credits</th><th class="text-end">Sections</th><th style="width:1%"></th></tr></thead>
                <tbody>
                @foreach ($courses as $course)
                    <tr>
                        <td><span class="badge text-bg-light border font-monospace">{{ $course->code }}</span></td>
                        <td class="fw-semibold">{{ $course->title }}</td>
                        <td class="small text-secondary">{{ $course->faculty->code }}</td>
                        <td class="text-end">{{ $course->credit_hours }}</td>
                        <td class="text-end">{{ $course->class_sections_count }}</td>
                        <td class="text-nowrap text-end">
                            <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" class="d-inline"
                                  onsubmit="return confirm('Delete {{ $course->code }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $courses->links() }}</div>
    @endif
</x-page-card>
@endsection
