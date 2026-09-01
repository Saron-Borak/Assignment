<div class="row g-3">
    <div class="col-md-6">
        <label for="course_id" class="form-label">Course <span class="text-danger">*</span></label>
        <select id="course_id" name="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
            <option value="">Choose a course...</option>
            @foreach ($courses as $course)
                <option value="{{ $course->id }}" @selected(old('course_id', $section->course_id) == $course->id)>
                    {{ $course->code }} - {{ $course->title }}
                </option>
            @endforeach
        </select>
        @error('course_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="semester_id" class="form-label">Semester <span class="text-danger">*</span></label>
        <select id="semester_id" name="semester_id" class="form-select @error('semester_id') is-invalid @enderror" required>
            <option value="">Choose a semester...</option>
            @foreach ($semesters as $semester)
                <option value="{{ $semester->id }}" @selected(old('semester_id', $section->semester_id ?? optional($semesters->firstWhere('is_active', true))->id) == $semester->id)>
                    {{ $semester->name }}{{ $semester->is_active ? ' (active)' : '' }}
                </option>
            @endforeach
        </select>
        @error('semester_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2">
        <label for="section_code" class="form-label">Section <span class="text-danger">*</span></label>
        <input type="text" id="section_code" name="section_code" maxlength="10"
               class="form-control text-uppercase @error('section_code') is-invalid @enderror"
               value="{{ old('section_code', $section->section_code) }}" required placeholder="A">
        @error('section_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="lecturer_id" class="form-label">Lecturer <span class="text-danger">*</span></label>
        <select id="lecturer_id" name="lecturer_id" class="form-select @error('lecturer_id') is-invalid @enderror" required>
            <option value="">Choose a lecturer...</option>
            @foreach ($lecturers as $lecturer)
                <option value="{{ $lecturer->id }}" @selected(old('lecturer_id', $section->lecturer_id) == $lecturer->id)>
                    {{ $lecturer->displayName() }} ({{ $lecturer->staff_no }})
                </option>
            @endforeach
        </select>
        @error('lecturer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="room" class="form-label">Default room</label>
        <input type="text" id="room" name="room" maxlength="50" class="form-control @error('room') is-invalid @enderror"
               value="{{ old('room', $section->room) }}" placeholder="B-204">
        @error('room')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="capacity" class="form-label">Capacity <span class="text-danger">*</span></label>
        <input type="number" id="capacity" name="capacity" min="1" max="500"
               class="form-control @error('capacity') is-invalid @enderror"
               value="{{ old('capacity', $section->capacity ?? 40) }}" required>
        @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<hr class="my-4">

<h3 class="h6 text-secondary text-uppercase mb-1" style="letter-spacing:.06em">Weekly timetable</h3>
<p class="small text-secondary">
    Lecturers generate a whole semester of attendance sessions from these slots.
    Leave a row blank to skip it.
</p>

@php($rows = old('schedules', $schedules ?: [[]]))
@php($rows = count($rows) ? $rows : [[]])

<div id="scheduleRows">
    @foreach ($rows as $i => $row)
        <div class="row g-2 align-items-end mb-2 schedule-row">
            <div class="col-sm-4">
                <label class="form-label small">Day</label>
                <select name="schedules[{{ $i }}][day_of_week]" class="form-select form-select-sm">
                    <option value="">-- none --</option>
                    @foreach (\App\Models\ClassSchedule::DAYS as $num => $day)
                        <option value="{{ $num }}" @selected(($row['day_of_week'] ?? null) == $num)>{{ $day }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label small">Start</label>
                <input type="time" name="schedules[{{ $i }}][start_time]" class="form-control form-control-sm"
                       value="{{ substr((string) ($row['start_time'] ?? ''), 0, 5) }}">
            </div>
            <div class="col-sm-3">
                <label class="form-label small">End</label>
                <input type="time" name="schedules[{{ $i }}][end_time]" class="form-control form-control-sm"
                       value="{{ substr((string) ($row['end_time'] ?? ''), 0, 5) }}">
            </div>
            <div class="col-sm-2">
                <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-row">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    @endforeach
</div>

<button type="button" id="addScheduleRow" class="btn btn-sm btn-outline-secondary mt-1">
    <i class="bi bi-plus-lg me-1"></i>Add another slot
</button>

@error('schedules.*.end_time')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
@error('schedules.*.start_time')<div class="text-danger small mt-2">{{ $message }}</div>@enderror

@push('scripts')
<script>
    (function () {
        const container = document.getElementById('scheduleRows');
        // Continue numbering from the rows already rendered so re-submitted
        // input keeps its array indexes.
        let nextIndex = container.querySelectorAll('.schedule-row').length;

        document.getElementById('addScheduleRow').addEventListener('click', function () {
            const first = container.querySelector('.schedule-row');
            const clone = first.cloneNode(true);

            clone.querySelectorAll('select, input').forEach(function (field) {
                field.name = field.name.replace(/schedules\[\d+\]/, 'schedules[' + nextIndex + ']');
                field.value = '';
            });

            container.appendChild(clone);
            nextIndex++;
        });

        container.addEventListener('click', function (event) {
            const button = event.target.closest('.remove-row');
            if (!button) return;

            const rows = container.querySelectorAll('.schedule-row');
            if (rows.length === 1) {
                // Keep one row so there is always something to clone from.
                rows[0].querySelectorAll('select, input').forEach(f => f.value = '');
                return;
            }
            button.closest('.schedule-row').remove();
        });
    })();
</script>
@endpush
