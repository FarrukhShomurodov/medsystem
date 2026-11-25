{{-- The fields of the transfer create and edit forms. Expects $transfer (null on create), $warehouses, $pharmacies, $medications and $lines. --}}
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label required" for="from_warehouse_id">Со склада</label>
        <select id="from_warehouse_id" name="from_warehouse_id" class="form-select select2 @error('from_warehouse_id') is-invalid @enderror" required>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @selected(old('from_warehouse_id', $transfer?->from_warehouse_id) == $warehouse->id)>
                    {{ $warehouse->name }}
                </option>
            @endforeach
        </select>
        @error('from_warehouse_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="to_pharmacy_id">В аптеку</label>
        <select id="to_pharmacy_id" name="to_pharmacy_id" class="form-select select2 @error('to_pharmacy_id') is-invalid @enderror" required>
            @foreach ($pharmacies as $pharmacy)
                <option value="{{ $pharmacy->id }}" @selected(old('to_pharmacy_id', $transfer?->to_pharmacy_id) == $pharmacy->id)>
                    {{ $pharmacy->name }}
                </option>
            @endforeach
        </select>
        @error('to_pharmacy_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="notes">Примечание</label>
        <input type="text" id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" maxlength="255"
               value="{{ old('notes', $transfer?->notes) }}">
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<h2 class="h3 mt-4 mb-3">Препараты</h2>

@error('medications')
    <div class="invalid-feedback mb-2">{{ $message }}</div>
@enderror

{{-- A transfer keeps at least one line. Old input can skip keys (a removed line): new lines continue after the highest one. --}}
<div data-row-list data-row-list-min="1" data-row-list-next="{{ $lines === [] ? 0 : max(array_map('intval', array_keys($lines))) + 1 }}">
    <div class="vstack gap-3" id="medications-container" data-row-list-items>
        @foreach ($lines as $index => $line)
            @include('admin.transfers._line', ['index' => $index, 'line' => $line])
        @endforeach
    </div>

    {{-- A new line; __INDEX__ becomes its number. Its select2 is set up when the line is added. --}}
    <template id="medication-line-template" data-row-list-template>
        @include('admin.transfers._line', ['index' => '__INDEX__', 'line' => []])
    </template>

    <button type="button" class="btn btn-outline-primary mt-3" id="add-medication" data-row-add>
        <i class="bx bx-plus" aria-hidden="true"></i>Добавить препарат
    </button>
</div>
