@extends('layouts.app')
@section('title', 'Mark register')
@section('heading', 'Register · '.$session->classSection->fullLabel())
@section('subheading', $session->session_date->format('l, d F Y').' · '.$session->timeRange())

@section('toolbar')
    @if ($session->isOpen())
        <a href="{{ route('lecturer.sessions.qr', $session) }}" class="btn btn-success btn-sm no-print">
            <i class="bi bi-qr-code me-1"></i>Show check-in code
        </a>
    @endif
    <a href="{{ route('lecturer.sessions.show', $session) }}" class="btn btn-outline-secondary btn-sm ms-2 no-print">
        <i class="bi bi-eye me-1"></i>Summary
    </a>
@endsection

@section('content')
@if ($roster->isEmpty())
    <x-page-card>
        <x-empty-state icon="bi-person-plus" title="Nobody is enrolled in this class"
                       message="An administrator must add students to the roster before a register can be taken." />
    </x-page-card>
@else
<form method="POST" action="{{ route('lecturer.sessions.mark.store', $session) }}" id="registerForm">
    @csrf @method('PUT')

    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap align-items-center gap-3">
            <div class="me-auto">
                <div class="fw-semibold">{{ $roster->count() }} students on the roster</div>
                <div class="small text-secondary">
                    <x-status-badge :status="$session->status" />
                    <span class="ms-2">Arrivals after {{ $session->lateThreshold()->format('H:i') }} count as late.</span>
                </div>
            </div>

            <div class="btn-group btn-group-sm no-print" role="group" aria-label="Mark everyone">
                <span class="btn btn-light disabled border">Mark all</span>
                @foreach ($statuses as $status)
                    <button type="button" class="btn btn-outline-secondary mark-all" data-status="{{ $status->value }}">
                        {{ $status->label() }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <x-page-card>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:1%">#</th>
                        <th>Student</th>
                        <th style="width:1%" class="text-nowrap">Attendance</th>
                        <th style="width:22%">Remark</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($roster as $index => $student)
                    @php($record = $existing->get($student->id))
                    @php($current = old('marks.'.$student->id, $record?->status->value ?? \App\Enums\AttendanceStatus::Present->value))
                    <tr class="register-row">
                        <td class="text-secondary small">{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $student->user->name }}</div>
                            <div class="small text-secondary">
                                {{ $student->student_no }}
                                @if ($record?->marked_via)
                                    <span class="ms-1 badge text-bg-light border" style="font-size:.65rem">{{ $record->marked_via->label() }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm text-nowrap" role="group" aria-label="Attendance for {{ $student->user->name }}">
                                @foreach ($statuses as $status)
                                    @php($id = 'm'.$student->id.$status->value)
                                    <input type="radio" class="btn-check" name="marks[{{ $student->id }}]"
                                           id="{{ $id }}" value="{{ $status->value }}" @checked($current === $status->value)>
                                    <label class="btn btn-outline-{{ str_replace('text-bg-', '', $status->badgeClass()) }}" for="{{ $id }}">
                                        <i class="bi {{ $status->icon() }} me-1"></i>{{ $status->label() }}
                                    </label>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <input type="text" name="remarks[{{ $student->id }}]" maxlength="255"
                                   class="form-control form-control-sm"
                                   value="{{ old('remarks.'.$student->id, $record?->remarks) }}"
                                   placeholder="Optional">
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white d-flex flex-wrap gap-2 no-print">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save register</button>

            @unless ($session->isClosed())
                <button type="submit" name="then" value="close" class="btn btn-outline-danger"
                        onclick="return confirm('Save the register and close this session? Anyone left unmarked will be recorded absent.')">
                    <i class="bi bi-lock me-1"></i>Save and close session
                </button>
            @endunless

            <a href="{{ route('lecturer.classes.show', $session->classSection) }}" class="btn btn-outline-secondary ms-auto">Cancel</a>
        </div>
    </x-page-card>
</form>

@push('scripts')
<script>
    document.querySelectorAll('.mark-all').forEach(function (button) {
        button.addEventListener('click', function () {
            const status = this.dataset.status;
            document.querySelectorAll('#registerForm input[type=radio][value="' + status + '"]')
                .forEach(radio => { radio.checked = true; });
        });
    });
</script>
@endpush
@endif
@endsection
