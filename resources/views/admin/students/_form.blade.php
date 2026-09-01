@php($isEdit = $student->exists)

<h3 class="h6 text-secondary text-uppercase mb-3" style="letter-spacing:.06em">Account</h3>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label for="name" class="form-label">Full name <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $student->user?->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $student->user?->email) }}" required placeholder="name@student.eamu.edu">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="phone" class="form-label">Phone</label>
        <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror"
               value="{{ old('phone', $student->user?->phone) }}">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check form-switch mb-2">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                   @checked(old('is_active', $student->user?->is_active ?? true))>
            <label class="form-check-label" for="is_active">Account can sign in</label>
        </div>
    </div>
    <div class="col-md-6">
        <label for="password" class="form-label">
            Password @unless($isEdit)<span class="text-danger">*</span>@endunless
        </label>
        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
               autocomplete="new-password" @unless($isEdit) required @endunless>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @if ($isEdit)<div class="form-text">Leave blank to keep the current password.</div>@endif
    </div>
    <div class="col-md-6">
        <label for="password_confirmation" class="form-label">Confirm password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
               autocomplete="new-password" @unless($isEdit) required @endunless>
    </div>
</div>

<h3 class="h6 text-secondary text-uppercase mb-3" style="letter-spacing:.06em">Student record</h3>
<div class="row g-3">
    <div class="col-md-4">
        <label for="student_no" class="form-label">Student number <span class="text-danger">*</span></label>
        <input type="text" id="student_no" name="student_no" class="form-control @error('student_no') is-invalid @enderror"
               value="{{ old('student_no', $student->student_no) }}" required placeholder="EAMU-2026-0001">
        @error('student_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="intake_year" class="form-label">Intake year <span class="text-danger">*</span></label>
        <input type="number" id="intake_year" name="intake_year" min="2000" max="{{ date('Y') + 1 }}"
               class="form-control @error('intake_year') is-invalid @enderror"
               value="{{ old('intake_year', $student->intake_year ?? date('Y')) }}" required>
        @error('intake_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-5">
        <label for="program_id" class="form-label">Program <span class="text-danger">*</span></label>
        <select id="program_id" name="program_id" class="form-select @error('program_id') is-invalid @enderror" required>
            <option value="">Choose a program...</option>
            @foreach ($programs as $program)
                <option value="{{ $program->id }}" @selected(old('program_id', $student->program_id) == $program->id)>
                    {{ $program->code }} - {{ $program->name }}
                </option>
            @endforeach
        </select>
        @error('program_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="status" class="form-label">Enrollment status <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach (\App\Enums\StudentStatus::cases() as $case)
                <option value="{{ $case->value }}" @selected(old('status', $student->status?->value ?? 'active') === $case->value)>
                    {{ $case->label() }}
                </option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
