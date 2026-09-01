@extends('layouts.app')
@section('title', 'My attendance')
@section('heading', 'My attendance')
@section('subheading', $semester ? $semester->name : 'All semesters')

@section('toolbar')
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm no-print"><i class="bi bi-printer me-1"></i>Print</button>
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
</form>

<x-page-card :subtitle="'You must maintain at least '.$threshold.'% attendance in every class.'" title="Attendance by class">
    @if ($stats->isEmpty())
        <x-empty-state icon="bi-journal-x" title="Nothing to show"
                       message="You are not enrolled in any class for this period." />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Class</th><th>Lecturer</th>
                        <th class="text-end">Held</th><th class="text-end">Present</th>
                        <th class="text-end">Late</th><th class="text-end">Absent</th><th class="text-end">Excused</th>
                        <th style="width:170px">Attendance</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($stats as $row)
                    <tr class="{{ $row->at_risk ? 'row-at-risk' : '' }}">
                        <td>
                            <a href="{{ route('student.attendance.show', $row->class_section_id) }}" class="fw-semibold text-decoration-none">
                                {{ $row->course_code }}-{{ $row->section_code }}
                            </a>
                            <div class="small text-secondary">{{ $row->course_title }}</div>
                        </td>
                        <td class="small text-secondary">{{ $row->lecturer_name }}</td>
                        <td class="text-end">{{ $row->held }}</td>
                        <td class="text-end text-success">{{ $row->present }}</td>
                        <td class="text-end text-warning-emphasis">{{ $row->late }}</td>
                        <td class="text-end text-danger">{{ $row->absent }}</td>
                        <td class="text-end text-secondary">{{ $row->excused }}</td>
                        <td><x-percentage-bar :percentage="$row->percentage" :threshold="$threshold" /></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-page-card>
@endsection
