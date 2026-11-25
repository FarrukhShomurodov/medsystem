{{-- An excipient of the medication form: $index (a number, or __INDEX__ in the template) and $substance ([substance_name, substance_dosage]). --}}
<div class="additional-substance-group border rounded p-3 pe-5 position-relative" data-row>
    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-row-remove aria-label="Убрать вспомогательное вещество"></button>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label required" for="additional_substance-{{ $index }}-substance_name">Вспомогательное вещество (название)</label>
            <div class="input-group input-group-flat">
                <span class="input-group-text"><i class="bx bx-list-check" aria-hidden="true"></i></span>
                <input type="text" id="additional_substance-{{ $index }}-substance_name" name="additional_substance[{{ $index }}][substance_name]"
                       class="form-control @error("additional_substance.$index.substance_name") is-invalid @enderror"
                       value="{{ $substance['substance_name'] ?? '' }}" required>
            </div>
            @error("additional_substance.$index.substance_name")
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label required" for="additional_substance-{{ $index }}-substance_dosage">Вспомогательное вещество (дозировка)</label>
            <div class="input-group input-group-flat">
                <span class="input-group-text"><i class="bx bx-sort-alt-2" aria-hidden="true"></i></span>
                <input type="text" id="additional_substance-{{ $index }}-substance_dosage" name="additional_substance[{{ $index }}][substance_dosage]"
                       class="form-control @error("additional_substance.$index.substance_dosage") is-invalid @enderror"
                       value="{{ $substance['substance_dosage'] ?? '' }}" required>
            </div>
            @error("additional_substance.$index.substance_dosage")
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
