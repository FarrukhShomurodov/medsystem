{{-- A medication line of the transfer form: $index (a number, or __INDEX__ in the template) and $line ([medication_id, quantity]). --}}
<div class="medication-group row g-2 align-items-end" data-row>
    <div class="col-md-7">
        <label class="form-label required" for="medications-{{ $index }}-medication_id">Препарат</label>
        <select id="medications-{{ $index }}-medication_id" name="medications[{{ $index }}][medication_id]"
                class="form-select select2 @error("medications.$index.medication_id") is-invalid @enderror" required>
            @foreach ($medications as $medication)
                <option value="{{ $medication->id }}" @selected(($line['medication_id'] ?? null) == $medication->id)>{{ $medication->name }}</option>
            @endforeach
        </select>
        @error("medications.$index.medication_id")
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label required" for="medications-{{ $index }}-quantity">Кол-во</label>
        <input type="number" min="1" id="medications-{{ $index }}-quantity" name="medications[{{ $index }}][quantity]"
               class="form-control @error("medications.$index.quantity") is-invalid @enderror"
               value="{{ $line['quantity'] ?? '' }}" required>
        @error("medications.$index.quantity")
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-2">
        <button type="button" class="btn btn-outline-danger w-100" data-row-remove>
            <i class="bx bx-x" aria-hidden="true"></i>Убрать
        </button>
    </div>
</div>
