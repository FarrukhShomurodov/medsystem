{{-- The fields of an equipment item (create and edit); $equipment is null on create. --}}
<div class="row g-3">
    <div class="col-12">
        <label class="form-label required" for="name">Название</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-rename" aria-hidden="true"></i></span>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                   placeholder="Название" value="{{ old('name', $equipment?->name) }}" required>
        </div>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="equipment_type_id">Тип оборудования</label>
        <select id="equipment_type_id" name="equipment_type_id" class="form-select select2 @error('equipment_type_id') is-invalid @enderror" required>
            @foreach ($equipmentTypes as $type)
                <option value="{{ $type->id }}" @selected($type->id == old('equipment_type_id', $equipment?->equipment_type_id))>{{ $type->name }}</option>
            @endforeach
        </select>
        @error('equipment_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="manufacturer_id">Производитель</label>
        <select id="manufacturer_id" name="manufacturer_id" class="form-select select2 @error('manufacturer_id') is-invalid @enderror" required>
            @foreach ($manufacturers as $manufacturer)
                <option value="{{ $manufacturer->id }}" @selected($manufacturer->id == old('manufacturer_id', $equipment?->manufacturer_id))>{{ $manufacturer->name }}</option>
            @endforeach
        </select>
        @error('manufacturer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="serial_number">Серийный номер</label>
        <input type="text" id="serial_number" name="serial_number" class="form-control @error('serial_number') is-invalid @enderror"
               value="{{ old('serial_number', $equipment?->serial_number) }}" required>
        @error('serial_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="price">Цена</label>
        <input type="number" id="price" name="price" class="form-control @error('price') is-invalid @enderror"
               min="0" step="0.01" value="{{ old('price', $equipment?->price) }}" required>
        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="date_purchase">Дата покупки</label>
        <input type="text" id="date_purchase" name="date_purchase" class="form-control flatpickr-date @error('date_purchase') is-invalid @enderror"
               placeholder="ГГГГ-ММ-ДД" value="{{ old('date_purchase', $equipment?->date_purchase) }}" required>
        @error('date_purchase')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="calibration_date">Дата калибровки</label>
        <input type="text" id="calibration_date" name="calibration_date" class="form-control flatpickr-date @error('calibration_date') is-invalid @enderror"
               placeholder="ГГГГ-ММ-ДД" value="{{ old('calibration_date', $equipment?->calibration_date) }}" required>
        @error('calibration_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="term_date">Дата срока службы</label>
        <input type="text" id="term_date" name="term_date" class="form-control flatpickr-date @error('term_date') is-invalid @enderror"
               placeholder="ГГГГ-ММ-ДД" value="{{ old('term_date', $equipment?->term_date) }}" required>
        @error('term_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="term_notify_days">Оповестить за (дней)</label>
        <input type="number" id="term_notify_days" name="term_notify_days" class="form-control @error('term_notify_days') is-invalid @enderror"
               min="0" max="36500" value="{{ old('term_notify_days', $equipment?->term_notify_days) }}" required>
        @error('term_notify_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="upcoming_service_at">Ближайшее обслуживание через (дней)</label>
        <input type="number" id="upcoming_service_at" name="upcoming_service_at" class="form-control @error('upcoming_service_at') is-invalid @enderror"
               min="0" max="36500" value="{{ old('upcoming_service_at', $equipment?->upcoming_service_at) }}" required>
        @error('upcoming_service_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="repair">Ремонт</label>
        <input type="text" id="repair" name="repair" class="form-control @error('repair') is-invalid @enderror"
               value="{{ old('repair', $equipment?->repair) }}" required>
        @error('repair')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="location">Местоположение</label>
        <input type="text" id="location" name="location" class="form-control @error('location') is-invalid @enderror"
               value="{{ old('location', $equipment?->location) }}" required>
        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="staff_id">Ответственный сотрудник</label>
        <select id="staff_id" name="staff_id" class="form-select select2 @error('staff_id') is-invalid @enderror" required>
            @foreach ($staffList as $staff)
                <option value="{{ $staff->id }}" @selected($staff->id == old('staff_id', $equipment?->staff_id))>{{ $staff->full_name }}</option>
            @endforeach
        </select>
        @error('staff_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required" for="last_inspection">Дата последней проверки</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-calendar" aria-hidden="true"></i></span>
            <input type="text" id="last_inspection" name="last_inspection" class="form-control flatpickr-date @error('last_inspection') is-invalid @enderror"
                   placeholder="ГГГГ-ММ-ДД" value="{{ old('last_inspection', $equipment?->last_inspection) }}" required>
        </div>
        @error('last_inspection')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    @if ($equipment)
        <div class="col-md-6">
            <label class="form-label" for="status">Статус</label>
            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                @foreach (\App\Enums\EquipmentStatus::options() as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $equipment->status?->value) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    @endif
</div>
