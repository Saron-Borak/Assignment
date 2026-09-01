<div class="row g-3">
    <div class="col-md-4">
        <label for="code" class="form-label">Program code <span class="text-danger">*</span></label>
        <input type="text" id="code" name="code" maxlength="20"
               class="form-control text-uppercase @error('code') is-invalid @enderror"
               value="{{ old('code', $program->code) }}" required placeholder="BSCS">
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8">
        <label for="name" class="form-label">Program name <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $program->name) }}" required placeholder="BSc Computer Science">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8">
        <label for="faculty_id" class="form-label">Faculty <span class="text-danger">*</span></label>
        <select id="faculty_id" name="faculty_id" class="form-select @error('faculty_id') is-invalid @enderror" required>
            <option value="">Choose a faculty…</option>
            @foreach ($faculties as $faculty)
                <option value="{{ $faculty->id }}" @selected(old('faculty_id', $program->faculty_id) == $faculty->id)>
                    {{ $faculty->code }} — {{ $faculty->name }}
                </option>
            @endforeach
        </select>
        @error('faculty_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
