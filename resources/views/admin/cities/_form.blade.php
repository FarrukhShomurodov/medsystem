{{-- The fields of the city form (create and edit); $city is null on create. --}}
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label required" for="name">Название</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-rename" aria-hidden="true"></i></span>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                   placeholder="Название" value="{{ old('name', $city?->name) }}" required>
        </div>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="region_id">Область</label>
        <select id="region_id" name="region_id" class="form-select select2 @error('region_id') is-invalid @enderror"
                data-placeholder="Выберите область" required>
            <option value="" disabled @selected(old('region_id', $city?->region_id) === null)>Выберите область</option>
            @foreach ($regions as $region)
                <option value="{{ $region->id }}" @selected($region->id == old('region_id', $city?->region_id))>{{ $region->name }}</option>
            @endforeach
        </select>
        @error('region_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
