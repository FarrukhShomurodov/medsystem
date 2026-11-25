{{-- The fields of a price list service (create and edit); $service is null on create. --}}
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label required" for="name">Наименование</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-rename" aria-hidden="true"></i></span>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                   placeholder="Наименование" value="{{ old('name', $service?->name) }}" required>
        </div>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="code">Код</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-barcode" aria-hidden="true"></i></span>
            <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror"
                   placeholder="Код" value="{{ old('code', $service?->code) }}">
        </div>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label required" for="price">Стоимость (с учётом НДС)</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-dollar" aria-hidden="true"></i></span>
            <input type="number" id="price" name="price" class="form-control @error('price') is-invalid @enderror"
                   placeholder="Стоимость" min="0" step="0.01" value="{{ old('price', $service?->price) }}" required>
        </div>
        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="discount">Скидка</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-tag" aria-hidden="true"></i></span>
            <input type="number" id="discount" name="discount" class="form-control @error('discount') is-invalid @enderror"
                   placeholder="Скидка" min="0" step="1" value="{{ old('discount', $service?->discount) }}">
            <span class="input-group-text">%</span>
        </div>
        @error('discount')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="currency">Валюта</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-money" aria-hidden="true"></i></span>
            <input type="text" id="currency" name="currency" class="form-control @error('currency') is-invalid @enderror"
                   placeholder="Валюта" value="{{ old('currency', $service?->currency) }}">
        </div>
        @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="description">Описание</label>
        <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                  placeholder="Описание" rows="3">{{ old('description', $service?->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="service_group_id">Группа</label>
        <select id="service_group_id" name="service_group_id" class="form-select select2 @error('service_group_id') is-invalid @enderror"
                data-placeholder="Выберите группу" required>
            <option value="" disabled @selected(old('service_group_id', $service?->service_group_id) === null)>Выберите группу</option>
            @foreach ($groups as $group)
                <option value="{{ $group->id }}" @selected($group->id == old('service_group_id', $service?->service_group_id))>{{ $group->name }}</option>
            @endforeach
        </select>
        @error('service_group_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="service_type_id">Тип</label>
        <select id="service_type_id" name="service_type_id" class="form-select select2 @error('service_type_id') is-invalid @enderror"
                data-placeholder="Выберите тип" required>
            <option value="" disabled @selected(old('service_type_id', $service?->service_type_id) === null)>Выберите тип</option>
            @foreach ($types as $type)
                <option value="{{ $type->id }}" @selected($type->id == old('service_type_id', $service?->service_type_id))>{{ $type->name }}</option>
            @endforeach
        </select>
        @error('service_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- A switch posts 1 when on; the hidden field before it posts 0 when it is off. --}}
    <div class="col-12">
        <input type="hidden" name="is_active" value="0">
        <label class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                @checked(old('is_active', $service?->is_active ?? true))>
            <span class="form-check-label">Активна</span>
        </label>
        @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror

        <input type="hidden" name="is_appointment" value="0">
        <label class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" name="is_appointment" value="1"
                @checked(old('is_appointment', $service?->is_appointment ?? true))>
            <span class="form-check-label">По записи</span>
        </label>
        @error('is_appointment')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
