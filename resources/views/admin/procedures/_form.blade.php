{{-- The fields of the procedure form (create and edit); $procedure is null on create. --}}
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label required" for="name">Название</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-rename" aria-hidden="true"></i></span>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                   placeholder="Название" value="{{ old('name', $procedure?->name) }}" required>
        </div>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="price">Стоимость</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-dollar" aria-hidden="true"></i></span>
            {{-- The price may have tiyins: keep them (the edit form used to round them away). --}}
            <input type="number" id="price" name="price" class="form-control @error('price') is-invalid @enderror"
                   placeholder="Стоимость" min="0" step="0.01" value="{{ old('price', $procedure?->price) }}" required>
        </div>
        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
