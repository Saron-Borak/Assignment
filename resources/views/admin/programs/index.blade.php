@extends('layouts.app')
@section('title', 'Programs')
@section('heading', 'Programs')
@section('subheading', 'Degree programs students are admitted into')

@section('toolbar')
    <a href="{{ route('admin.programs.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New program</a>
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

    @if ($programs->isEmpty())
        <x-empty-state icon="bi-diagram-3" title="No programs yet" message="Programs group students under a faculty.">
            <a href="{{ route('admin.programs.create') }}" class="btn btn-sm btn-primary">Create a program</a>
        </x-empty-state>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Code</th><th>Name</th><th>Faculty</th><th class="text-end">Students</th><th style="width:1%"></th></tr></thead>
                <tbody>
                @foreach ($programs as $program)
                    <tr>
                        <td><span class="badge text-bg-light border font-monospace">{{ $program->code }}</span></td>
                        <td class="fw-semibold">{{ $program->name }}</td>
                        <td class="small text-secondary">{{ $program->faculty->name }}</td>
                        <td class="text-end">{{ $program->students_count }}</td>
                        <td class="text-nowrap text-end">
                            <a href="{{ route('admin.programs.edit', $program) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.programs.destroy', $program) }}" class="d-inline"
                                  onsubmit="return confirm('Delete {{ $program->code }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $programs->links() }}</div>
    @endif
</x-page-card>
@endsection
