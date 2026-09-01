<div class="row g-3">
    <div class="col-md-4">
        <label for="code" class="form-label">Faculty code <span class="text-danger">*</span></label>
        <input type="text" id="code" name="code" maxlength="20"
               class="form-control text-uppercase @error('code') is-invalid @enderror"
               value="{{ old('code', $faculty->code) }}" required placeholder="FCIT">
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8">
        <label for="name" class="form-label">Faculty name <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $faculty->name) }}" required placeholder="Faculty of Computing and Information Technology">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
