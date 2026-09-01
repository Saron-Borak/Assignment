@extends('layouts.app')
@section('title', 'Faculties')
@section('heading', 'Faculties')
@section('subheading', 'Top-level academic divisions of the university')

@section('toolbar')
    <a href="{{ route('admin.faculties.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>New faculty
    </a>
@endsection

@section('content')
<x-page-card>
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-2">
            <div class="col-sm-6 col-lg-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search code or name">
                    <button class="btn btn-outline-secondary">Search</button>
                </div>
            </div>
        </form>
    </div>

    @if ($faculties->isEmpty())
        <x-empty-state icon="bi-building" title="No faculties yet" message="Create a faculty before adding programs, courses or lecturers.">
            <a href="{{ route('admin.faculties.create') }}" class="btn btn-sm btn-primary">Create the first faculty</a>
        </x-empty-state>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Code</th><th>Name</th>
                        <th class="text-end">Programs</th><th class="text-end">Courses</th><th class="text-end">Lecturers</th>
                        <th style="width:1%"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($faculties as $faculty)
                        <tr>
                            <td><span class="badge text-bg-light border font-monospace">{{ $faculty->code }}</span></td>
                            <td class="fw-semibold">{{ $faculty->name }}</td>
                            <td class="text-end">{{ $faculty->programs_count }}</td>
                            <td class="text-end">{{ $faculty->courses_count }}</td>
                            <td class="text-end">{{ $faculty->lecturers_count }}</td>
                            <td class="text-nowrap text-end">
                                <a href="{{ route('admin.faculties.edit', $faculty) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.faculties.destroy', $faculty) }}" class="d-inline"
                                      onsubmit="return confirm('Delete {{ $faculty->code }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $faculties->links() }}</div>
    @endif
</x-page-card>
@endsection
