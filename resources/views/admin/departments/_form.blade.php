{{-- The fields of the department form (create and edit); $department is null on create. --}}
@php
    $selectedRooms = array_map('intval', (array) old('rooms', $department?->rooms->pluck('id')->all() ?? []));
@endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label required" for="name">Название</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-rename" aria-hidden="true"></i></span>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                   placeholder="Название" value="{{ old('name', $department?->name) }}" required>
        </div>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="floor_id">Этаж</label>
        <select id="floor_id" name="floor_id" class="form-select select2 @error('floor_id') is-invalid @enderror">
            <option value="">Без этажа</option>
            @foreach ($floors as $floor)
                <option value="{{ $floor->id }}" @selected($floor->id == old('floor_id', $department?->floor_id))>{{ $floor->name }}</option>
            @endforeach
        </select>
        @error('floor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="warehouse_id">Склад</label>
        <select id="warehouse_id" name="warehouse_id" class="form-select select2 @error('warehouse_id') is-invalid @enderror">
            <option value="">Без склада</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @selected($warehouse->id == old('warehouse_id', $department?->warehouse_id))>{{ $warehouse->name }}</option>
            @endforeach
        </select>
        @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="rooms">Палаты</label>
        <select id="rooms" name="rooms[]" class="form-select select2 @if ($errors->has('rooms') || $errors->has('rooms.*')) is-invalid @endif"
                data-placeholder="Выберите палаты" multiple required>
            @foreach ($wardRooms as $wardRoom)
                <option value="{{ $wardRoom->id }}" @selected(in_array($wardRoom->id, $selectedRooms, true))>
                    {{ $wardRoom->room_number }} (Койки: {{ $wardRoom->beds_count }})
                </option>
            @endforeach
        </select>
        @error('rooms')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @error('rooms.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
