{{--
    The three steps of the patient service form (create and edit), inside its <form>.
    $order: the patient service being edited, or null; $catalogue: the services that can be ordered;
    $maps: the examination maps of the order's patient (edit only); $selectedPatient: the patient
    shown as picked (of a refused submission, else of the order), or null.
    resources/js/pages/patient-service-form.js turns the patient field into a search of the register
    (lib/patient-picker.js, api.patients.index) and reloads the map list when another patient is picked.
--}}
@use('App\Support\Display')
@php
    $insurance = $order?->insurance_policy ?? [];
    $mapId = old('examination_map_id', $order?->examination_map_id);
    $birthDate = $selectedPatient?->birth_date ? \Illuminate\Support\Carbon::parse($selectedPatient->birth_date)->format('d.m.Y') : null;
@endphp

<div id="basic-data" class="content" role="tabpanel" aria-labelledby="basic-data-trigger">
    <div class="content-header mb-3">
        <h2 class="h3 mb-0">Основные данные</h2>
    </div>
    <div class="row g-3">
        <div class="col-lg-4">
            <label class="form-label required" for="patient_id">Пациент</label>
            {{-- No .select2 class: the page module sets up the search (frontend.md). --}}
            <select id="patient_id" name="patient_id" class="form-select @error('patient_id') is-invalid @enderror"
                    data-placeholder="Начните вводить ФИО пациента" data-patients-url="{{ route('api.patients.index') }}" required>
                <option value="" disabled @selected($selectedPatient === null)>Выберите пациента</option>
                @if ($selectedPatient)
                    <option value="{{ $selectedPatient->id }}" selected>{{ $selectedPatient->full_name }}@if ($birthDate) ({{ $birthDate }})@endif</option>
                @endif
            </select>
            @error('patient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-lg-4">
            <label class="form-label" for="examination_map_id">Карта обследования</label>
            <select id="examination_map_id" name="examination_map_id"
                    class="form-select select2 @error('examination_map_id') is-invalid @enderror"
                    data-maps-url="{{ route('api.examination-maps.by-patient', ['patient' => '__PATIENT__']) }}"
                    data-maps-patient="{{ $order?->patient_id }}"
                    data-selected="{{ $mapId }}">
                <option value="">Без карты</option>
                @foreach ($maps ?? [] as $map)
                    <option value="{{ $map->id }}" @selected($map->id == $mapId)>{{ $map->patient->full_name }} — {{ Display::date($map->admission_date) }}</option>
                @endforeach
            </select>
            @error('examination_map_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-lg-4">
            <label class="form-label required" for="service_id">Услуга</label>
            <select id="service_id" name="service_id" class="form-select select2 @error('service_id') is-invalid @enderror"
                    data-placeholder="Выберите услугу" required>
                <option value="" disabled @selected(old('service_id', $order?->service_id) === null)>Выберите услугу</option>
                @foreach ($catalogue as $item)
                    <option value="{{ $item->id }}" @selected($item->id == old('service_id', $order?->service_id))>{{ $item->name }}</option>
                @endforeach
            </select>
            @error('service_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12">
            <label class="form-label required" for="place">Место оказания</label>
            <select id="place" name="place" class="form-select select2 @error('place') is-invalid @enderror" required>
                @foreach (\App\Enums\ServicePlace::options() as $value => $label)
                    <option value="{{ $value }}" @selected(old('place', $order?->place?->value) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('place')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12 d-flex justify-content-end">
            <button type="button" class="btn btn-primary btn-next">Далее<i class="bx bx-chevron-right ms-1 me-0" aria-hidden="true"></i></button>
        </div>
    </div>
</div>

<div id="details" class="content" role="tabpanel" aria-labelledby="details-trigger">
    <div class="content-header mb-3">
        <h2 class="h3 mb-0">Детали</h2>
    </div>
    <div class="row g-3">
        <div class="col-lg-6">
            <label class="form-label required" for="service_date">Дата услуги</label>
            <div class="input-group input-group-flat">
                <span class="input-group-text"><i class="bx bx-calendar" aria-hidden="true"></i></span>
                <input type="text" id="service_date" name="service_date"
                       class="form-control flatpickr-date @error('service_date') is-invalid @enderror"
                       placeholder="ГГГГ-ММ-ДД" value="{{ old('service_date', $order?->service_date) }}" required>
            </div>
            @error('service_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-lg-6">
            <label class="form-label" for="service_time">Время услуги</label>
            <div class="input-group input-group-flat">
                <span class="input-group-text"><i class="bx bx-time-five" aria-hidden="true"></i></span>
                <input type="text" id="service_time" name="service_time"
                       class="form-control flatpickr-clock @error('service_time') is-invalid @enderror"
                       placeholder="ЧЧ:ММ" value="{{ old('service_time', $order?->service_time) }}">
            </div>
            @error('service_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12">
            <label class="form-label" for="result">Результаты</label>
            <textarea id="result" name="result" class="form-control @error('result') is-invalid @enderror"
                      rows="4">{{ old('result', $order?->result) }}</textarea>
            @error('result')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12 d-flex justify-content-between">
            <button type="button" class="btn btn-prev"><i class="bx bx-chevron-left" aria-hidden="true"></i>Назад</button>
            <button type="button" class="btn btn-primary btn-next">Далее<i class="bx bx-chevron-right ms-1 me-0" aria-hidden="true"></i></button>
        </div>
    </div>
</div>

<div id="additional_data" class="content" role="tabpanel" aria-labelledby="additional_data-trigger">
    <div class="content-header mb-3">
        <h2 class="h3 mb-0">Финансовая информация</h2>
    </div>
    <div class="row g-3">
        <div class="col-lg-6">
            <label class="form-label" for="payment_status">Статус оплаты</label>
            <select id="payment_status" name="payment[status]" class="form-select select2 @error('payment.status') is-invalid @enderror">
                <option value="">Не выбрано</option>
                @foreach (\App\Enums\PaymentStatus::options() as $value => $label)
                    <option value="{{ $value }}" @selected(old('payment.status', $order?->paymentStatus()?->value) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('payment.status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-lg-6">
            <label class="form-label" for="payment_method">Метод оплаты</label>
            <select id="payment_method" name="payment[method]" class="form-select select2 @error('payment.method') is-invalid @enderror">
                <option value="">Не выбрано</option>
                @foreach (\App\Enums\PaymentMethod::options() as $value => $label)
                    <option value="{{ $value }}" @selected(old('payment.method', $order?->paymentMethod()?->value) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('payment.method')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-lg-4">
            <label class="form-label" for="insurance_company">Страховая компания</label>
            <div class="input-group input-group-flat">
                <span class="input-group-text"><i class="bx bx-building" aria-hidden="true"></i></span>
                <input type="text" id="insurance_company" name="insurance_policy[company_name]"
                       class="form-control @error('insurance_policy.company_name') is-invalid @enderror"
                       value="{{ old('insurance_policy.company_name', $insurance['company_name'] ?? null) }}">
            </div>
            @error('insurance_policy.company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-lg-4">
            <label class="form-label" for="policy_number">Номер полиса</label>
            <div class="input-group input-group-flat">
                <span class="input-group-text"><i class="bx bx-id-card" aria-hidden="true"></i></span>
                <input type="text" id="policy_number" name="insurance_policy[insurance_policy_number]"
                       class="form-control @error('insurance_policy.insurance_policy_number') is-invalid @enderror"
                       value="{{ old('insurance_policy.insurance_policy_number', $insurance['insurance_policy_number'] ?? null) }}">
            </div>
            @error('insurance_policy.insurance_policy_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-lg-4">
            <label class="form-label" for="coverage">Процент покрытия</label>
            <div class="input-group input-group-flat">
                <input type="number" id="coverage" name="insurance_policy[percentage_of_coverage]"
                       class="form-control @error('insurance_policy.percentage_of_coverage') is-invalid @enderror"
                       min="0" max="100" step="any"
                       value="{{ old('insurance_policy.percentage_of_coverage', $insurance['percentage_of_coverage'] ?? null) }}">
                <span class="input-group-text">%</span>
            </div>
            @error('insurance_policy.percentage_of_coverage')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12 d-flex justify-content-between">
            <button type="button" class="btn btn-prev"><i class="bx bx-chevron-left" aria-hidden="true"></i>Назад</button>
            <button class="btn btn-success" type="submit"><i class="bx bx-save" aria-hidden="true"></i>Сохранить</button>
        </div>
    </div>
</div>
