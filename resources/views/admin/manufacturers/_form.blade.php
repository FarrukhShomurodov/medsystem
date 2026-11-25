{{-- The fields of the create and edit forms. Expects $manufacturer (null on create). --}}
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label required" for="name">Название</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-rename" aria-hidden="true"></i></span>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $manufacturer?->name) }}" required>
        </div>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="country_of_manufacture">Страна производства</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-world" aria-hidden="true"></i></span>
            <input type="text" id="country_of_manufacture" name="country_of_manufacture"
                   class="form-control @error('country_of_manufacture') is-invalid @enderror"
                   value="{{ old('country_of_manufacture', $manufacturer?->country_of_manufacture) }}" required>
        </div>
        @error('country_of_manufacture')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
