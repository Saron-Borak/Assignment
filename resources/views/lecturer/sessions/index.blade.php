@extends('layouts.app')
@section('title', 'Class sessions')
@section('heading', 'Class sessions')
@section('subheading', 'Every session across the classes you teach')

@section('content')
<x-page-card>
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4 col-lg-3">
                <label for="section_id" class="form-label small mb-1">Class</label>
                <select id="section_id" name="section_id" class="form-select form-select-sm">
                    <option value="">All my classes</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}" @selected(request('section_id') == $section->id)>
                            {{ $section->course->code }}-{{ $section->section_code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3 col-lg-2">
                <label for="status" class="form-label small mb-1">Status</label>
                <select id="status" name="status" class="form-select form-select-sm">
                    <option value="">Any</option>
                    @foreach (\App\Enums\SessionStatus::cases() as $case)
                        <option value="{{ $case->value }}" @selected(request('status') === $case->value)>{{ $case->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label for="from" class="form-label small mb-1">From</label>
                <input type="date" id="from" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
            </div>
            <div class="col-auto">
                <label for="to" class="form-label small mb-1">To</label>
                <input type="date" id="to" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-primary">Filter</button></div>
        </form>
    </div>

    @if ($sessions->isEmpty())
        <x-empty-state icon="bi-calendar-x" title="No sessions found"
                       message="Adjust your filters, or generate sessions from a class timetable." />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr><th>Date</th><th>Class</th><th>Time</th><th>Status</th><th class="text-end">Attended</th><th class="text-end">Action</th></tr>
                </thead>
                <tbody>
                @foreach ($sessions as $session)
                    <tr>
                        <td>
                            <a href="{{ route('lecturer.sessions.show', $session) }}" class="fw-semibold text-decoration-none">
                                {{ $session->session_date->format('D, d M Y') }}
                            </a>
                        </td>
                        <td class="small">
                            <div class="fw-semibold">{{ $session->classSection->course->code }}-{{ $session->classSection->section_code }}</div>
                            <div class="text-secondary">{{ $session->classSection->course->title }}</div>
                        </td>
                        <td class="small text-nowrap">{{ $session->timeRange() }}</td>
                        <td><x-status-badge :status="$session->status" /></td>
                        <td class="text-end small">{{ $session->attended_count }} / {{ $session->records_count }}</td>
                        <td class="text-end text-nowrap">
                            @if ($session->isOpen())
                                <a href="{{ route('lecturer.sessions.qr', $session) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-qr-code"></i></a>
                            @endif
                            <a href="{{ route('lecturer.sessions.mark', $session) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil-square"></i></a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $sessions->links() }}</div>
    @endif
</x-page-card>
@endsection
