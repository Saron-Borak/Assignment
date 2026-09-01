@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Hello, '.$student->user->name)
@section('subheading', $student->student_no.' · '.$student->program->name)

@section('content')
    @if ($openNow->isNotEmpty())
        <div class="alert alert-success d-flex align-items-center flex-wrap gap-2">
            <i class="bi bi-broadcast fs-5"></i>
            <div class="me-auto">
                <strong>A class is open for check-in right now.</strong>
                <div class="small">
                    {{ $openNow->map(fn ($s) => $s->classSection->course->code.'-'.$s->classSection->section_code)->join(', ') }}
                </div>
            </div>
            <a href="{{ route('student.check-in.create') }}" class="btn btn-sm btn-success">
                <i class="bi bi-qr-code-scan me-1"></i>Check in now
            </a>
        </div>
    @endif

    @if ($atRisk->isNotEmpty())
        <div class="alert alert-danger">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                <div>
                    <strong>Your attendance is below the {{ $threshold }}% requirement in {{ $atRisk->count() }} class(es).</strong>
                    <ul class="mb-0 mt-1 small ps-3">
                        @foreach ($atRisk as $row)
                            <li>
                                {{ $row->course_code }}-{{ $row->section_code }} — {{ $row->course_title }}
                                (<strong>{{ number_format($row->percentage, 1) }}%</strong>)
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-card label="Overall attendance" :value="number_format($overall, 1).'%'" icon="bi-graph-up-arrow"
                         :variant="$overall >= $threshold ? 'success' : 'danger'"
                         :hint="'Requirement: '.$threshold.'%'" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card label="My classes" :value="$stats->count()" icon="bi-journal-bookmark" variant="primary"
                         :href="route('student.attendance.index')" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card label="Sessions attended" :value="$stats->sum('attended').' / '.$stats->sum('countable')"
                         icon="bi-check-circle" variant="info" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card label="Absences" :value="$stats->sum('absent')" icon="bi-x-circle" variant="danger" />
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <x-page-card title="Attendance by class" :subtitle="$semester?->name ?? 'Current enrollment'">
                @if ($stats->isEmpty())
                    <x-empty-state icon="bi-journal-x" title="You are not enrolled in any class yet"
                                   message="Your faculty office manages enrollments." />
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Class</th><th class="text-end">Attended</th><th style="width:170px">Attendance</th></tr></thead>
                            <tbody>
                            @foreach ($stats as $row)
                                <tr class="{{ $row->at_risk ? 'row-at-risk' : '' }}">
                                    <td>
                                        <a href="{{ route('student.attendance.show', $row->class_section_id) }}" class="fw-semibold text-decoration-none">
                                            {{ $row->course_code }}-{{ $row->section_code }}
                                        </a>
                                        <div class="small text-secondary">{{ $row->course_title }}</div>
                                    </td>
                                    <td class="text-end small">{{ $row->attended }} / {{ $row->countable }}</td>
                                    <td><x-percentage-bar :percentage="$row->percentage" :threshold="$threshold" /></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-page-card>
        </div>

        <div class="col-lg-5">
            <x-page-card title="Recent attendance">
                @if ($recent->isEmpty())
                    <x-empty-state icon="bi-clock-history" title="No attendance recorded yet"
                                   message="Records appear here once your classes begin." />
                @else
                    <div class="list-group list-group-flush">
                        @foreach ($recent as $record)
                            <div class="list-group-item d-flex align-items-center gap-2">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="small fw-semibold text-truncate">
                                        {{ $record->session->classSection->course->code }}-{{ $record->session->classSection->section_code }}
                                    </div>
                                    <div class="text-secondary" style="font-size:.78rem">
                                        {{ $record->session->session_date->format('D, d M Y') }} · {{ $record->session->timeRange() }}
                                    </div>
                                </div>
                                <x-status-badge :status="$record->status" />
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-page-card>
        </div>
    </div>
@endsection
