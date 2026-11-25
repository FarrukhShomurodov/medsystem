{{-- The medication wizard of the create and edit pages. Expects $medication (null on create), $groups and $manufacturers. --}}
@php
    $composition = $medication?->composition;
    $application = $medication?->application;
    $storage = $medication?->storage;
    $license = $medication?->licenseAndCertification;
    $data = $medication?->additionalData;

    // Text fields of a step: name => [label, input type, icon, current value, column, required].
    $basicFields = [
        'registration_number' => ['Регистрационный номер', 'text', 'bx-hash', $medication?->registration_number, 'col-md-6 col-xl-4', true],
        'registration_date' => ['Дата регистрации', 'date', 'bx-calendar', $medication?->registration_date, 'col-md-6 col-xl-4', true],
        'min_deduction' => ['Минимальное списание', 'number', 'bx-minus-circle', $medication?->min_deduction, 'col-md-6 col-xl-4', true],
        'min_quantity' => ['Минимальное количество', 'text', 'bx-layer', $medication?->min_quantity, 'col-md-6 col-xl-4', true],
        'notify_before_days' => ['Уведомить о сроке годности за (дней)', 'text', 'bx-bell', $medication?->notify_before_days, 'col-md-6 col-xl-4', true],
        'notify_min_qty' => ['Уведомить при остатке не больше', 'text', 'bx-bell', $medication?->notify_min_qty, 'col-md-6 col-xl-4', true],
    ];
    $applicationFields = [
        'administration_method' => ['Способ применения', 'text', 'bx-capsule', $application?->administration_method, 'col-md-6 col-xl-4', true],
        'dosage_regimen' => ['Режим дозирования', 'text', 'bx-time', $application?->dosage_regimen, 'col-md-6 col-xl-4', true],
        'drug_interactions' => ['Взаимодействие с другими препаратами', 'text', 'bx-git-merge', $application?->drug_interactions, 'col-md-6 col-xl-4', true],
    ];
    $dataFields = [
        'manufacturing_license_number' => ['Номер лицензии на производство', 'text', 'bx-file', $data?->manufacturing_license_number, 'col-md-6', false],
        'additional_usage_conditions' => ['Дополнительные условия использования', 'text', 'bx-list-check', $data?->additional_usage_conditions, 'col-md-6', false],
        'prescription_category' => ['Категория рецепта', 'text', 'bx-category', $data?->prescription_category, 'col-md-6', false],
        'specific_features' => ['Индивидуальные особенности', 'text', 'bx-bulb', $data?->specific_features, 'col-md-6', false],
    ];
    $files = [
        'sales_license' => 'Лицензия на продажу',
        'quality_certificate' => 'Сертификат качества',
        'clinical_trials' => 'Клинические исследования',
        'registration_documents' => 'Регистрационные документы',
    ];
    $storageConditions = [
        'Хранить в холодильнике (2–8°C)',
        'Комнатная температура (15–25°C)',
        'Хранить в защищённом от света месте',
        'Заморозка (ниже 0°C)',
    ];

    $substances = old('additional_substance', $composition?->additionalSubstance
        ->map(fn ($substance) => ['substance_name' => $substance->substance_name, 'substance_dosage' => $substance->substance_dosage])
        ->all() ?? []);
    // The form needs at least one excipient: an empty line to start with.
    $substances = $substances === [] ? [['substance_name' => '', 'substance_dosage' => '']] : $substances;

    $steps = [
        'basic-data' => 'Основные данные',
        'composition' => 'Состав',
        'application' => 'Применение',
        'storage' => 'Хранение',
        'license' => 'Лицензии и сертификация',
        'data' => 'Прочие данные',
    ];
@endphp

<div class="bs-stepper wizard-vertical vertical">
    <div class="bs-stepper-header" role="tablist">
        @foreach ($steps as $pane => $label)
            <div class="step" data-target="#{{ $pane }}">
                <button type="button" class="step-trigger" role="tab" id="{{ $pane }}-trigger" aria-controls="{{ $pane }}">
                    <span class="bs-stepper-circle">{{ $loop->iteration }}</span>
                    <span class="bs-stepper-label">{{ $label }}</span>
                </button>
            </div>
        @endforeach
    </div>

    <div class="bs-stepper-content">
        <form method="POST" enctype="multipart/form-data"
              action="{{ $medication ? route('medications.update', $medication->id) : route('medications.store') }}">
            @csrf
            @if ($medication)
                @method('PUT')
            @endif

            @foreach ($steps as $pane => $heading)
                <div id="{{ $pane }}" class="content" role="tabpanel" aria-labelledby="{{ $pane }}-trigger">
                    <div class="content-header mb-3">
                        <h2 class="h3 mb-0">{{ $heading }}</h2>
                    </div>
                    <div class="row g-3">
                        @switch($pane)
                            @case('basic-data')
                                <div class="col-md-6 col-xl-4">
                                    <label class="form-label required" for="name">Название</label>
                                    <div class="input-group input-group-flat">
                                        <span class="input-group-text"><i class="bx bx-rename" aria-hidden="true"></i></span>
                                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name', $medication?->name) }}" required>
                                    </div>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-xl-4">
                                    <label class="form-label required" for="release_form">Форма выпуска</label>
                                    <select id="release_form" name="release_form" class="form-select select2 @error('release_form') is-invalid @enderror" required>
                                        @foreach (\App\Models\Medication::RELEASEFORM as $form)
                                            <option value="{{ $form }}" @selected($form == old('release_form', $medication?->release_form))>{{ $form }}</option>
                                        @endforeach
                                    </select>
                                    @error('release_form')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-xl-4">
                                    <label class="form-label required" for="dosage">Дозировка</label>
                                    {{-- Text, not a number: the dosage carries its unit ("500 мг"), as MedicationRequest stores it. --}}
                                    <div class="input-group input-group-flat">
                                        <span class="input-group-text"><i class="bx bx-droplet" aria-hidden="true"></i></span>
                                        <input type="text" id="dosage" name="dosage" class="form-control @error('dosage') is-invalid @enderror"
                                               value="{{ old('dosage', $medication?->dosage) }}" maxlength="255" placeholder="Например, 500 мг" required>
                                    </div>
                                    @error('dosage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-xl-4">
                                    <label class="form-label required" for="group_id">Фармакологическая группа</label>
                                    <select id="group_id" name="group_id" class="form-select select2 @error('group_id') is-invalid @enderror" required>
                                        @foreach ($groups as $group)
                                            <option value="{{ $group->id }}" @selected($group->id == old('group_id', $medication?->group_id))>{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('group_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-xl-4">
                                    <label class="form-label required" for="manufacturer_id">Производитель</label>
                                    <select id="manufacturer_id" name="manufacturer_id" class="form-select select2 @error('manufacturer_id') is-invalid @enderror" required>
                                        @foreach ($manufacturers as $manufacturer)
                                            <option value="{{ $manufacturer->id }}" @selected($manufacturer->id == old('manufacturer_id', $medication?->manufacturer_id))>{{ $manufacturer->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('manufacturer_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                @include('admin.medications._fields', ['fields' => $basicFields])
                                @break

                            @case('composition')
                                <div class="col-md-6">
                                    <label class="form-label required" for="active_substance_name">Название активного вещества</label>
                                    <div class="input-group input-group-flat">
                                        <span class="input-group-text"><i class="bx bx-capsule" aria-hidden="true"></i></span>
                                        <input type="text" id="active_substance_name" name="active_substance_name"
                                               class="form-control @error('active_substance_name') is-invalid @enderror"
                                               value="{{ old('active_substance_name', $composition?->active_substance_name) }}" required>
                                    </div>
                                    @error('active_substance_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label required" for="active_substance_dosage">Дозировка активного вещества</label>
                                    <div class="input-group input-group-flat">
                                        <span class="input-group-text"><i class="bx bx-droplet" aria-hidden="true"></i></span>
                                        <input type="text" id="active_substance_dosage" name="active_substance_dosage"
                                               class="form-control @error('active_substance_dosage') is-invalid @enderror"
                                               value="{{ old('active_substance_dosage', $composition?->active_substance_dosage) }}" required>
                                    </div>
                                    @error('active_substance_dosage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Old input can skip keys (a removed line): new lines continue after the highest one. --}}
                                <div class="col-12" data-row-list data-row-list-next="{{ max(array_map('intval', array_keys($substances))) + 1 }}">
                                    @error('additional_substance')
                                        <div class="invalid-feedback mb-2">{{ $message }}</div>
                                    @enderror
                                    <div class="vstack gap-3" id="additional-substance-wrapper" data-row-list-items>
                                        @foreach ($substances as $index => $substance)
                                            @include('admin.medications._substance', ['index' => $index, 'substance' => $substance])
                                        @endforeach
                                    </div>
                                    {{-- A new excipient line; __INDEX__ becomes its number. --}}
                                    <template id="additional-substance-template" data-row-list-template>
                                        @include('admin.medications._substance', ['index' => '__INDEX__', 'substance' => []])
                                    </template>
                                    <button type="button" class="btn btn-outline-primary mt-3" id="add-additional-substance" data-row-add>
                                        <i class="bx bx-plus" aria-hidden="true"></i>Добавить вспомогательное вещество
                                    </button>
                                </div>
                                @break

                            @case('application')
                                @include('admin.medications._fields', ['fields' => $applicationFields])
                                @break

                            @case('storage')
                                <div class="col-md-6">
                                    <label class="form-label required" for="storage_temperature">Температурный режим хранения</label>
                                    <div class="input-group input-group-flat">
                                        <span class="input-group-text"><i class="bx bxs-thermometer" aria-hidden="true"></i></span>
                                        <input type="number" id="storage_temperature" name="storage_temperature"
                                               class="form-control @error('storage_temperature') is-invalid @enderror"
                                               value="{{ old('storage_temperature', $storage?->storage_temperature) }}" required>
                                    </div>
                                    @error('storage_temperature')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    @php $condition = old('storage_conditions', $storage?->storage_conditions); @endphp
                                    <label class="form-label required" for="storage_conditions">Условия хранения</label>
                                    <select id="storage_conditions" name="storage_conditions"
                                            class="form-select select2 @error('storage_conditions') is-invalid @enderror" required>
                                        <option value="" disabled @selected(! $condition)>Выберите условие хранения</option>
                                        @foreach ($storageConditions as $option)
                                            <option value="{{ $option }}" @selected($condition == $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    @error('storage_conditions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @break

                            @case('license')
                                @foreach ($files as $field => $label)
                                    <div class="col-md-6">
                                        <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                                        <div class="input-group">
                                            <input type="file" id="{{ $field }}" name="{{ $field }}" accept=".pdf,.jpg,.png"
                                                   class="form-control @error($field) is-invalid @enderror">
                                            @if ($license?->$field)
                                                <a href="{{ asset('storage/'.$license->$field) }}" target="_blank" rel="noopener" class="btn">
                                                    <i class="bx bx-download" aria-hidden="true"></i>Скачать
                                                </a>
                                            @endif
                                        </div>
                                        @error($field)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                                <div class="col-12">
                                    <div class="form-hint">PDF, JPG или PNG, до 2 МБ каждый.</div>
                                </div>
                                @break

                            @case('data')
                                @include('admin.medications._fields', ['fields' => $dataFields])
                                @break
                        @endswitch

                        <div class="col-12 d-flex">
                            @unless ($loop->first)
                                <button class="btn btn-prev"><i class="bx bx-chevron-left" aria-hidden="true"></i>Назад</button>
                            @endunless
                            @if ($loop->last)
                                <button type="submit" class="btn btn-primary ms-auto">Сохранить</button>
                            @else
                                <button class="btn btn-primary btn-next ms-auto">Далее<i class="bx bx-chevron-right ms-2 me-0" aria-hidden="true"></i></button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </form>
    </div>
</div>
