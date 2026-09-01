@extends('layouts.app')
@section('title', 'At-risk students')
@section('heading', 'Students below '.$threshold.'%')
@section('subheading', $semester ? $semester->name : 'All semesters')

@section('toolbar')
    <a href="{{ route('admin.reports.low-attendance.export', request()->query()) }}"
       class="btn btn-outline-success btn-sm no-print">
        <i class="bi bi-filetype-csv me-1"></i>Download CSV
    </a>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm ms-2 no-print"><i class="bi bi-printer me-1"></i>Print</button>
@endsection

@section('content')
<form method="GET" class="row g-2 align-items-center mb-3 no-print">
    <div class="col-sm-4 col-lg-3">
        <select name="semester_id" class="form-select form-select-sm">
            <option value="">All semesters</option>
            @foreach ($semesters as $option)
                <option value="{{ $option->id }}" @selected($semester?->id === $option->id)>{{ $option->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-sm btn-primary">Apply</button></div>
    <div class="col-auto ms-auto small text-secondary">{{ $rows->count() }} at-risk enrollment(s)</div>
</form>

<x-page-card>
    @if ($rows->isEmpty())
        <x-empty-state icon="bi-emoji-smile" title="Nobody is below the threshold"
                       :message="'Every student is meeting the '.$threshold.'% minimum attendance requirement.'" />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Student</th><th>Program</th><th>Class</th>
                        <th class="text-end">Attended</th><th class="text-end">Absent</th>
                        <th style="width:170px">Attendance</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($rows as $row)
                    <tr class="row-at-risk">
                        <td>
                            <a href="{{ route('admin.reports.student', $row->student_id) }}" class="fw-semibold text-decoration-none">{{ $row->name }}</a>
                            <div class="small text-secondary">{{ $row->student_no }} · {{ $row->email }}</div>
                        </td>
                        <td class="small text-secondary">{{ $row->program_name }}</td>
                        <td class="small">
                            <div class="fw-semibold">{{ $row->course_code }}-{{ $row->section_code }}</div>
                            <div class="text-secondary">{{ $row->course_title }}</div>
                        </td>
                        <td class="text-end">{{ $row->attended }} / {{ $row->countable }}</td>
                        <td class="text-end text-danger fw-semibold">{{ $row->absent }}</td>
                        <td><x-percentage-bar :percentage="$row->percentage" :threshold="$threshold" /></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-page-card>
@endsection
