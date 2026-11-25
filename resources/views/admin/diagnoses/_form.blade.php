{{-- The code and name of a diagnosis (create and edit); $diagnosis is null on create. --}}
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label required" for="code">Код</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-barcode" aria-hidden="true"></i></span>
            <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror"
                   placeholder="Код" value="{{ old('code', $diagnosis?->code) }}" required>
        </div>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-8">
        <label class="form-label required" for="name">Название</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-rename" aria-hidden="true"></i></span>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                   placeholder="Название" value="{{ old('name', $diagnosis?->name) }}" required>
        </div>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
