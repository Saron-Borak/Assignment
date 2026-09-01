<div class="row g-3">
    <div class="col-md-4">
        <label for="code" class="form-label">Semester code <span class="text-danger">*</span></label>
        <input type="text" id="code" name="code" maxlength="20"
               class="form-control @error('code') is-invalid @enderror"
               value="{{ old('code', $semester->code) }}" required placeholder="2026-S2">
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8">
        <label for="name" class="form-label">Display name <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $semester->name) }}" required placeholder="2026 Semester 2">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="start_date" class="form-label">Start date <span class="text-danger">*</span></label>
        <input type="date" id="start_date" name="start_date"
               class="form-control @error('start_date') is-invalid @enderror"
               value="{{ old('start_date', $semester->start_date?->format('Y-m-d')) }}" required>
        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="end_date" class="form-label">End date <span class="text-danger">*</span></label>
        <input type="date" id="end_date" name="end_date"
               class="form-control @error('end_date') is-invalid @enderror"
               value="{{ old('end_date', $semester->end_date?->format('Y-m-d')) }}" required>
        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                   @checked(old('is_active', $semester->is_active))>
            <label class="form-check-label" for="is_active">Make this the active semester</label>
        </div>
        <div class="form-text">Only one semester can be active. Activating this one stands the others down automatically.</div>
    </div>
</div>
