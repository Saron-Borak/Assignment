<div class="row g-3">
    <div class="col-md-3">
        <label for="code" class="form-label">Course code <span class="text-danger">*</span></label>
        <input type="text" id="code" name="code" maxlength="20"
               class="form-control text-uppercase @error('code') is-invalid @enderror"
               value="{{ old('code', $course->code) }}" required placeholder="CS201">
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-7">
        <label for="title" class="form-label">Course title <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title"
               class="form-control @error('title') is-invalid @enderror"
               value="{{ old('title', $course->title) }}" required placeholder="Database Systems">
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2">
        <label for="credit_hours" class="form-label">Credits <span class="text-danger">*</span></label>
        <input type="number" id="credit_hours" name="credit_hours" min="1" max="12"
               class="form-control @error('credit_hours') is-invalid @enderror"
               value="{{ old('credit_hours', $course->credit_hours ?? 3) }}" required>
        @error('credit_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8">
        <label for="faculty_id" class="form-label">Faculty <span class="text-danger">*</span></label>
        <select id="faculty_id" name="faculty_id" class="form-select @error('faculty_id') is-invalid @enderror" required>
            <option value="">Choose a faculty...</option>
            @foreach ($faculties as $faculty)
                <option value="{{ $faculty->id }}" @selected(old('faculty_id', $course->faculty_id) == $faculty->id)>
                    {{ $faculty->code }} - {{ $faculty->name }}
                </option>
            @endforeach
        </select>
        @error('faculty_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea id="description" name="description" rows="3"
                  class="form-control @error('description') is-invalid @enderror"
                  placeholder="Optional course outline">{{ old('description', $course->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
