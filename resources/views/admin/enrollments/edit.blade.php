@extends('layouts.app')
@section('title', 'Manage roster')
@section('heading', 'Roster · '.$section->fullLabel())
@section('subheading', $section->semester->name.' · '.$section->lecturer->displayName())

@section('toolbar')
    <a href="{{ route('admin.class-sections.show', $section) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to section
    </a>
@endsection

@section('content')
@php($activeCount = $enrolled->where('status', \App\Enums\EnrollmentStatus::Enrolled)->count())
<div class="row g-3">
    <div class="col-lg-6">
        <x-page-card :title="'Enrolled students ('.$activeCount.' / '.$section->capacity.')'">
            @if ($enrolled->isEmpty())
                <x-empty-state icon="bi-person-dash" title="Roster is empty" message="Pick students from the list on the right." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Student</th><th>Program</th><th>Status</th><th style="width:1%"></th></tr></thead>
                        <tbody>
                        @foreach ($enrolled as $enrollment)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $enrollment->student->user->name }}</div>
                                    <div class="small text-secondary">{{ $enrollment->student->student_no }}</div>
                                </td>
                                <td class="small text-secondary">{{ $enrollment->student->program->code }}</td>
                                <td>
                                    @if ($enrollment->status === \App\Enums\EnrollmentStatus::Enrolled)
                                        <span class="badge text-bg-success-subtle text-success-emphasis border border-success-subtle">Enrolled</span>
                                    @else
                                        <span class="badge text-bg-secondary">Dropped</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('admin.class-sections.enrollments.destroy', [$section, $enrollment]) }}"
                                          onsubmit="return confirm('Remove this student from the roster?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-page-card>
    </div>

    <div class="col-lg-6">
        <x-page-card title="Add students" subtitle="Only students not already on this roster are listed">
            <div class="card-header bg-white py-3 border-top">
                <form method="GET" class="row g-2">
                    <div class="col-sm-7">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name or student no.">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <select name="program_id" class="form-select form-select-sm">
                            <option value="">All programs</option>
                            @foreach ($programs as $program)
                                <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>{{ $program->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto"><button class="btn btn-sm btn-outline-secondary">Find</button></div>
                </form>
            </div>

            @if ($candidates->isEmpty())
                <x-empty-state icon="bi-search" title="No matching students"
                               message="Every matching student is already enrolled, or nobody matches your search." />
            @else
                <form method="POST" action="{{ route('admin.class-sections.enrollments.store', $section) }}">
                    @csrf
                    <div class="table-responsive" style="max-height:460px; overflow-y:auto">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th style="width:1%"><input type="checkbox" class="form-check-input" id="checkAll"></th>
                                    <th>Student</th><th>Program</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($candidates as $candidate)
                                <tr>
                                    <td><input type="checkbox" class="form-check-input candidate" name="student_ids[]" value="{{ $candidate->id }}"></td>
                                    <td>
                                        <div class="fw-semibold">{{ $candidate->user->name }}</div>
                                        <div class="small text-secondary">{{ $candidate->student_no }}</div>
                                    </td>
                                    <td class="small text-secondary">{{ $candidate->program->code }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white">
                        <button class="btn btn-primary btn-sm"><i class="bi bi-person-plus me-1"></i>Enroll selected</button>
                        <span class="small text-secondary ms-2">Showing up to 100 matches.</span>
                    </div>
                </form>
            @endif
        </x-page-card>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('checkAll')?.addEventListener('change', function () {
        document.querySelectorAll('.candidate').forEach(box => { box.checked = this.checked; });
    });
</script>
@endpush
@endsection
