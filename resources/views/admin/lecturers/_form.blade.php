@php($isEdit = $lecturer->exists)

<h3 class="h6 text-secondary text-uppercase mb-3" style="letter-spacing:.06em">Account</h3>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label for="name" class="form-label">Full name <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $lecturer->user?->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $lecturer->user?->email) }}" required placeholder="name@eamu.edu">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="phone" class="form-label">Phone</label>
        <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror"
               value="{{ old('phone', $lecturer->user?->phone) }}">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check form-switch mb-2">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                   @checked(old('is_active', $lecturer->user?->is_active ?? true))>
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

<h3 class="h6 text-secondary text-uppercase mb-3" style="letter-spacing:.06em">Staff record</h3>
<div class="row g-3">
    <div class="col-md-4">
        <label for="staff_no" class="form-label">Staff number <span class="text-danger">*</span></label>
        <input type="text" id="staff_no" name="staff_no" class="form-control @error('staff_no') is-invalid @enderror"
               value="{{ old('staff_no', $lecturer->staff_no ?? ($suggestedStaffNo ?? '')) }}" required>
        @error('staff_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" id="title" name="title" maxlength="30" class="form-control @error('title') is-invalid @enderror"
               value="{{ old('title', $lecturer->title) }}" placeholder="Dr.">
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-5">
        <label for="faculty_id" class="form-label">Faculty <span class="text-danger">*</span></label>
        <select id="faculty_id" name="faculty_id" class="form-select @error('faculty_id') is-invalid @enderror" required>
            <option value="">Choose a faculty...</option>
            @foreach ($faculties as $faculty)
                <option value="{{ $faculty->id }}" @selected(old('faculty_id', $lecturer->faculty_id) == $faculty->id)>
                    {{ $faculty->code }} - {{ $faculty->name }}
                </option>
            @endforeach
        </select>
        @error('faculty_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
