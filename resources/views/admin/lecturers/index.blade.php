@extends('layouts.app')
@section('title', 'Lecturers')
@section('heading', 'Lecturers')
@section('subheading', 'Teaching staff and the classes assigned to them')

@section('toolbar')
    <a href="{{ route('admin.lecturers.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New lecturer</a>
@endsection

@section('content')
<x-page-card>
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-sm-6 col-lg-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name, email or staff no.">
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

    @if ($lecturers->isEmpty())
        <x-empty-state icon="bi-person-video3" title="No lecturers found" message="Lecturers need an account before they can be assigned a class.">
            <a href="{{ route('admin.lecturers.create') }}" class="btn btn-sm btn-primary">Add a lecturer</a>
        </x-empty-state>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Staff no.</th><th>Name</th><th>Faculty</th><th class="text-end">Classes</th><th>Status</th><th style="width:1%"></th></tr></thead>
                <tbody>
                @foreach ($lecturers as $lecturer)
                    <tr>
                        <td><span class="badge text-bg-light border font-monospace">{{ $lecturer->staff_no }}</span></td>
                        <td>
                            <a href="{{ route('admin.lecturers.show', $lecturer) }}" class="fw-semibold text-decoration-none">{{ $lecturer->displayName() }}</a>
                            <div class="small text-secondary">{{ $lecturer->user->email }}</div>
                        </td>
                        <td class="small text-secondary">{{ $lecturer->faculty->code }}</td>
                        <td class="text-end">{{ $lecturer->class_sections_count }}</td>
                        <td>
                            @if ($lecturer->user->is_active)
                                <span class="badge text-bg-success-subtle text-success-emphasis border border-success-subtle">Active</span>
                            @else
                                <span class="badge text-bg-secondary">Disabled</span>
                            @endif
                        </td>
                        <td class="text-nowrap text-end">
                            <a href="{{ route('admin.lecturers.edit', $lecturer) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.lecturers.destroy', $lecturer) }}" class="d-inline"
                                  onsubmit="return confirm('Delete {{ $lecturer->user->name }} and their account?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $lecturers->links() }}</div>
    @endif
</x-page-card>
@endsection
