{{-- The fields of the create and edit forms. Expects $material (null on create). --}}
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label required" for="name">Название</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-rename" aria-hidden="true"></i></span>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $material?->name) }}" required>
        </div>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="batch_number">Номер партии</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-barcode" aria-hidden="true"></i></span>
            <input type="text" id="batch_number" name="batch_number"
                   class="form-control @error('batch_number') is-invalid @enderror"
                   value="{{ old('batch_number', $material?->batch_number) }}" required>
        </div>
        @error('batch_number')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="entry_price_total">Сумма вх.</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-money" aria-hidden="true"></i></span>
            <input type="number" step="0.01" id="entry_price_total" name="entry_price_total"
                   class="form-control @error('entry_price_total') is-invalid @enderror"
                   value="{{ old('entry_price_total', $material?->entry_price_total) }}" required>
        </div>
        @error('entry_price_total')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="entry_price_unit">Цена вх. за ед.</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-dollar-circle" aria-hidden="true"></i></span>
            <input type="number" step="0.01" id="entry_price_unit" name="entry_price_unit"
                   class="form-control @error('entry_price_unit') is-invalid @enderror"
                   value="{{ old('entry_price_unit', $material?->entry_price_unit) }}" required>
        </div>
        @error('entry_price_unit')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
