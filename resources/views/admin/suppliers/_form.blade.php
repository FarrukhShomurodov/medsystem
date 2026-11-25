{{-- The fields of the create and edit forms. Expects $supplier (null on create) and $manufacturers. --}}
@php
    $selectedManufacturers = array_map('intval', (array) old('manufacturers', $supplier?->manufacturers->pluck('id')->all() ?? []));
@endphp

<div class="row g-3">
    <div class="col-md-6 col-xl-4">
        <label class="form-label required" for="name">Название</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-rename" aria-hidden="true"></i></span>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $supplier?->name) }}" required>
        </div>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 col-xl-4">
        <label class="form-label" for="contract_terms">Условия контракта</label>
        <div class="input-group">
            <input type="file" id="contract_terms" name="contract_terms" accept=".pdf,.jpg,.png"
                   class="form-control @error('contract_terms') is-invalid @enderror"
                   aria-describedby="contract_terms-hint">
            @if ($supplier?->contract_terms)
                <a href="{{ asset('storage/'.$supplier->contract_terms) }}" target="_blank" rel="noopener" class="btn">
                    <i class="bx bx-download" aria-hidden="true"></i>Скачать
                </a>
            @endif
        </div>
        <div class="form-hint" id="contract_terms-hint">PDF, JPG или PNG, до 2 МБ.</div>
        @error('contract_terms')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 col-xl-4">
        <label class="form-label required" for="phone">Телефон</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-phone" aria-hidden="true"></i></span>
            <input type="tel" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror"
                   value="{{ old('phone', $supplier?->phone) }}" autocomplete="tel" required>
        </div>
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 col-xl-4">
        <label class="form-label required" for="email">Email</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-envelope" aria-hidden="true"></i></span>
            <input type="text" id="email" name="email" inputmode="email" autocomplete="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $supplier?->email) }}" required>
        </div>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 col-xl-4">
        <label class="form-label required" for="address">Адрес</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-map" aria-hidden="true"></i></span>
            <input type="text" id="address" name="address" class="form-control @error('address') is-invalid @enderror"
                   value="{{ old('address', $supplier?->address) }}" required>
        </div>
        @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 col-xl-4">
        <label class="form-label required" for="delivery_schedule">Расписание поставок</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-calendar" aria-hidden="true"></i></span>
            <input type="text" id="delivery_schedule" name="delivery_schedule"
                   class="form-control @error('delivery_schedule') is-invalid @enderror"
                   value="{{ old('delivery_schedule', $supplier?->delivery_schedule) }}" required>
        </div>
        @error('delivery_schedule')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label required" for="manufacturers">Производители</label>
        <select id="manufacturers" name="manufacturers[]" multiple required data-placeholder="Выберите производителей"
                class="form-select select2 @error('manufacturers') is-invalid @enderror">
            @foreach ($manufacturers as $manufacturer)
                <option value="{{ $manufacturer->id }}" @selected(in_array($manufacturer->id, $selectedManufacturers, true))>
                    {{ $manufacturer->name }}
                </option>
            @endforeach
        </select>
        @error('manufacturers')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
