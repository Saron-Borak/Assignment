@extends('layouts.app')
@section('title', 'Students')
@section('heading', 'Students')
@section('subheading', 'Everyone enrolled at the university')

@section('toolbar')
    <a href="{{ route('admin.students.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New student</a>
@endsection

@section('content')
<x-page-card>
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-sm-6 col-lg-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name, email or student no.">
                </div>
            </div>
            <div class="col-sm-4 col-lg-3">
                <select name="program_id" class="form-select form-select-sm">
                    <option value="">All programs</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>{{ $program->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-outline-secondary">Filter</button></div>
            <div class="col-auto ms-auto small text-secondary">{{ $students->total() }} student(s)</div>
        </form>
    </div>

    @if ($students->isEmpty())
        <x-empty-state icon="bi-mortarboard" title="No students found" message="Adjust your filters or add a new student.">
            <a href="{{ route('admin.students.create') }}" class="btn btn-sm btn-primary">Add a student</a>
        </x-empty-state>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Student no.</th><th>Name</th><th>Program</th><th class="text-end">Classes</th><th>Status</th><th style="width:1%"></th></tr></thead>
                <tbody>
                @foreach ($students as $student)
                    <tr>
                        <td><span class="badge text-bg-light border font-monospace">{{ $student->student_no }}</span></td>
                        <td>
                            <a href="{{ route('admin.students.show', $student) }}" class="fw-semibold text-decoration-none">{{ $student->user->name }}</a>
                            <div class="small text-secondary">{{ $student->user->email }}</div>
                        </td>
                        <td class="small text-secondary">
                            {{ $student->program->code }}
                            <div class="text-body-tertiary">Intake {{ $student->intake_year }}</div>
                        </td>
                        <td class="text-end">{{ $student->enrollments_count }}</td>
                        <td><x-status-badge :status="$student->status" /></td>
                        <td class="text-nowrap text-end">
                            <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.students.destroy', $student) }}" class="d-inline"
                                  onsubmit="return confirm('Delete this student? Their attendance history will be removed too.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $students->links() }}</div>
    @endif
</x-page-card>
@endsection
