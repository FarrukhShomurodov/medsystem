{{-- The fields of the office form (create and edit); $office is null on create. --}}
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label required" for="name">Название</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-rename" aria-hidden="true"></i></span>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                   placeholder="Название" value="{{ old('name', $office?->name) }}" required>
        </div>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="floor_id">Этаж</label>
        <select id="floor_id" name="floor_id" class="form-select select2 @error('floor_id') is-invalid @enderror">
            <option value="">Без этажа</option>
            @foreach ($floors as $floor)
                <option value="{{ $floor->id }}" @selected($floor->id == old('floor_id', $office?->floor_id))>{{ $floor->name }}</option>
            @endforeach
        </select>
        @error('floor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
