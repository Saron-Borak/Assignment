@extends('layouts.app')
@section('title', $student->user->name)
@section('heading', $student->user->name)
@section('subheading', $student->student_no.' · '.$student->program->name)

@section('toolbar')
    <a href="{{ route('admin.reports.student', $student) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-bar-chart-line me-1"></i>Full report</a>
    <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-outline-secondary btn-sm ms-2"><i class="bi bi-pencil me-1"></i>Edit</a>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <x-page-card title="Student record">
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-secondary fw-normal">Email</dt><dd class="col-7">{{ $student->user->email }}</dd>
                    <dt class="col-5 text-secondary fw-normal">Phone</dt><dd class="col-7">{{ $student->user->phone ?: '-' }}</dd>
                    <dt class="col-5 text-secondary fw-normal">Program</dt><dd class="col-7">{{ $student->program->name }}</dd>
                    <dt class="col-5 text-secondary fw-normal">Faculty</dt><dd class="col-7">{{ $student->program->faculty->name }}</dd>
                    <dt class="col-5 text-secondary fw-normal">Intake</dt><dd class="col-7">{{ $student->intake_year }}</dd>
                    <dt class="col-5 text-secondary fw-normal">Status</dt><dd class="col-7"><x-status-badge :status="$student->status" /></dd>
                    <dt class="col-5 text-secondary fw-normal">Account</dt><dd class="col-7">{{ $student->user->is_active ? 'Active' : 'Disabled' }}</dd>
                </dl>
            </div>
        </x-page-card>
    </div>

    <div class="col-lg-8">
        @include('admin.students._attendance-table', ['stats' => $stats])
    </div>
</div>
@endsection
