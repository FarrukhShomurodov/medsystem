@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Препараты</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('medications.index') }}">Препараты </a> /</span>Редактировать
    </h6>

    <div class="alert-container position-fixed top-0 end-0 p-3" style="z-index: 10050;">
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div class="alert alert-solid-danger alert-dismissible d-flex align-items-center" role="alert">
                    <i class="bx bx-error-circle fs-4 me-2"></i>
                    {{ $error }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endforeach
        @endif
    </div>

    <div class="bs-stepper wizard-numbered wizard-vertical vertical shadow-sm mt-2">
        <div class="bs-stepper-header">
            <div class="step" data-target="#basic-data">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">1</span>
                    <span class="bs-stepper-label">Основные данные</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#composition">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">2</span>
                    <span class="bs-stepper-label">Состав</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#application">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">3</span>
                    <span class="bs-stepper-label">Применение</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#storage">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">4</span>
                    <span class="bs-stepper-label">Хранение</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#license">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">5</span>
                    <span class="bs-stepper-label">Лицензии и сертификация</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#data">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">6</span>
                    <span class="bs-stepper-label">Прочие данные</span>
                </button>
            </div>
        </div>

        <div class="bs-stepper-content">
            <form method="post" action="{{ route('medications.update', $medication->id) }}"
                  enctype="multipart/form-data">
                @csrf
                @method('put')

                <!-- Основные данные -->
                <div id="basic-data" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Основные данные</h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-company">Название <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('name') text-danger icon-border @enderror"><i
                                    class="bx bx-rename"></i></span>
                                <input type="text" id="basic-icon-default-company"
                                       class="form-control @error('name') is-invalid @enderror"
                                       placeholder="Название" aria-label="Название"
                                       name="name"
                                       value="{{ old('name', $medication->name) }}"
                                       aria-describedby="basic-icon-default-company2" required/>
                            </div>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="release_form">Форма выпуска<span
                                    class="text-danger">*</span></label>

                            <select id="release_form" name="release_form"
                                    class="form-control select2 @error('release_form') is-invalid @enderror"
                                    required>
                                @foreach(\App\Models\Medication::RELEASEFORM as $form)
                                    <option
                                        value="{{ $form }}" @selected($form == old('release_form', $medication->release_form))>
                                        {{ $form }}
                                    </option>
                                @endforeach
                            </select>

                            @error('release_form')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-dosage">Дозировка <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('dosage') text-danger icon-border @enderror"><i
                                    class="bx bx-rename"></i></span>
                                <input type="text" id="basic-icon-default-dosage"
                                       class="form-control @error('dosage') is-invalid @enderror"
                                       placeholder="Дозировка" aria-label="Дозировка"
                                       name="dosage"
                                       value="{{ old('dosage', $medication->dosage) }}"
                                       aria-describedby="basic-icon-default-company2" required/>
                            </div>
                            @error('dosage')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="group">Фармакологическая группы<span
                                    class="text-danger">*</span></label>

                            <select id="group" name="group_id"
                                    class="form-control select2 @error('group_id') is-invalid @enderror"
                                    required>
                                @foreach($groups as $group)
                                    <option
                                        value="{{ $group->id }}" @selected($group->id == old('group_id', $medication->group_id))>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('group_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="manufacturer">Производители<span
                                    class="text-danger">*</span></label>

                            <select id="manufacturer" name="manufacturer_id"
                                    class="form-control select2 @error('manufacturer_id') is-invalid @enderror"
                                    required>
                                @foreach($manufacturers as $manufacturer)
                                    <option
                                        value="{{ $manufacturer->id }}" @selected($manufacturer->id == old('manufacturer_id', $medication->manufacturer_id))>
                                        {{ $manufacturer->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('manufacturer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-registration_number">Регистрационный номер
                                <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('registration_number') text-danger icon-border @enderror"><i
                                    class="bx bx-rename"></i></span>
                                <input type="text" id="basic-icon-default-registration_number"
                                       class="form-control @error('registration_number') is-invalid @enderror"
                                       placeholder="Регистрационный номер" aria-label="Регистрационный номер"
                                       name="registration_number"
                                       value="{{ old('registration_number', $medication->registration_number) }}"
                                       aria-describedby="basic-icon-default-company2" required/>
                            </div>
                            @error('registration_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="basic-icon-registration_date">Дата регистрации <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-birth-date2"
                                      class="input-group-text @error('registration_date') text-danger icon-border @enderror">
                                    <i class="bx bx-calendar"></i>
                                </span>
                                <input type="text" name="registration_date"
                                       value="{{ old('registration_date', $medication->registration_date) }}"
                                       id="basic-icon-registration_date"
                                       class="form-control flatpickr-date @error('registration_date') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD" aria-describedby="basic-icon-birth-date2" required/>
                            </div>
                            @error('registration_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Navigation -->
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-label-secondary btn-prev" disabled>
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i> Назад
                            </button>
                            <button class="btn btn-primary btn-next">
                                Далее <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Состав -->
                <div id="composition" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Состав</h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-active_substance_name">Название активного
                                вещества <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-active_substance_name"
                                  class="input-group-text @error('active_substance_name') text-danger icon-border @enderror">
                                    <i class="bx bx-capsule"></i>
                            </span>
                                <input type="text" id="basic-icon-default-active_substance_name"
                                       class="form-control @error('active_substance_name') is-invalid @enderror"
                                       name="active_substance_name"
                                       value="{{ old('active_substance_name', $medication->composition->active_substance_name) }}"
                                       placeholder="Название активного вещества"
                                       aria-label="Название активного вещества"
                                       required
                                       aria-describedby="basic-icon-default-active_substance_name"/>
                            </div>

                            @error('active_substance_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-active_substance_dosage">Дозировка
                                активного вещества <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-active_substance_dosage"
                                  class="input-group-text @error('active_substance_dosage') text-danger icon-border @enderror">
                                 <i class="bx bx-droplet"></i>
                            </span>
                                <input type="text" id="basic-icon-default-active_substance_dosage"
                                       class="form-control @error('active_substance_dosage') is-invalid @enderror"
                                       name="active_substance_dosage"
                                       value="{{ old('active_substance_dosage', $medication->composition->active_substance_dosage) }}"
                                       placeholder="Дозировка активного вещества"
                                       required
                                       aria-label="Дозировка активного вещества"
                                       aria-describedby="basic-icon-default-active_substance_dosage"/>
                            </div>

                            @error('active_substance_dosage')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-substance_name">Вспомогательные вещества (
                                Название ) <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-substance_name"
                                  class="input-group-text @error('substance_name') text-danger icon-border @enderror">
                                 <i class="bx bx-list-check"></i>
                            </span>
                                <input type="text" id="basic-icon-default-substance_name"
                                       class="form-control @error('substance_name') is-invalid @enderror"
                                       name="substance_name"
                                       value="{{ old('substance_name', $medication->composition->substance_name) }}"
                                       placeholder="Вспомогательные вещества ( Название )"
                                       required
                                       aria-label="Вспомогательные вещества ( Название )"
                                       aria-describedby="basic-icon-default-substance_name"/>
                            </div>

                            @error('substance_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-substance_dosage">Вспомогательные вещества
                                ( Дозировка ) <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-substance_dosage"
                                  class="input-group-text @error('substance_dosage') text-danger icon-border @enderror">
                                   <i class="bx bx-sort-alt-2"></i>
                            </span>
                                <input type="text" id="basic-icon-default-substance_dosage"
                                       class="form-control @error('substance_dosage') is-invalid @enderror"
                                       name="substance_dosage"
                                       required
                                       value="{{ old('substance_dosage', $medication->composition->substance_dosage) }}"
                                       placeholder="Вспомогательные вещества ( Дозировка  )"
                                       aria-label="Вспомогательные вещества ( Дозировка )"
                                       aria-describedby="basic-icon-default-substance_dosage"/>
                            </div>

                            @error('substance_dosage')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <!-- Navigation -->
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev">
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i> Назад
                            </button>
                            <button class="btn btn-primary btn-next">
                                Далее <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Применение -->
                <div id="application" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Применение <span
                                class="text-danger">*</span></h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-administration_method">Способ
                                применения <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-administration_method"
                                  class="input-group-text @error('administration_method') text-danger icon-border @enderror">
                                  <i class="bx bx-capsule"></i>
                            </span>
                                <input type="text" id="basic-icon-default-administration_method"
                                       class="form-control @error('administration_method') is-invalid @enderror"
                                       name="administration_method"
                                       required
                                       value="{{ old('administration_method', $medication->application->administration_method) }}"
                                       placeholder="Способ применения" aria-label="Способ применения"
                                       aria-describedby="basic-icon-default-administration_method"/>
                            </div>
                            @error('administration_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-dosage_regimen">Режим дозирования <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-dosage_regimen"
                                  class="input-group-text @error('dosage_regimen') text-danger icon-border @enderror">
                                      <i class="bx bx-time"></i>
                            </span>
                                <input type="text" id="basic-icon-default-dosage_regimen"
                                       class="form-control @error('dosage_regimen') is-invalid @enderror"
                                       name="dosage_regimen"
                                       required
                                       value="{{ old('dosage_regimen',$medication->application->dosage_regimen) }}"
                                       placeholder="Режим дозирования" aria-label="Режим дозирования"
                                       aria-describedby="basic-icon-default-dosage_regimen"/>
                            </div>
                            @error('dosage_regimen')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label"
                                   for="basic-icon-default-drug_interactions">Взаимодействие с другими
                                препаратами <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-drug_interactions"
                                  class="input-group-text @error('drug_interactions') text-danger icon-border @enderror">
                                     <i class="bx bx-git-merge"></i>
                            </span>
                                <input type="text" id="basic-icon-default-drug_interactions"
                                       class="form-control @error('drug_interactions') is-invalid @enderror"
                                       name="drug_interactions"
                                       required
                                       value="{{ old('drug_interactions', $medication->application->drug_interactions) }}"
                                       placeholder="Побочные эффекты" aria-label="Побочные эффекты"
                                       aria-describedby="basic-icon-default-drug_interactions"/>
                            </div>
                            @error('drug_interactions')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Navigation -->
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev">
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i> Назад
                            </button>
                            <button class="btn btn-primary btn-next">
                                Далее <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Хранение -->
                <div id="storage" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Хранение</h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-storage_temperature">Температурный режим
                                хранения <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-storage_temperature"
                                  class="input-group-text @error('storage_temperature') text-danger icon-border @enderror">
                                  <i class='bx bxs-thermometer'></i>
                            </span>
                                <input type="number" id="basic-icon-default-storage_temperature"
                                       class="form-control @error('storage_temperature') is-invalid @enderror"
                                       name="storage_temperature"
                                       required
                                       value="{{ old('storage_temperature', $medication->storage->storage_temperature) }}"
                                       placeholder="Температурный режим хранения "
                                       aria-label="Температурный режим хранения "
                                       aria-describedby="basic-icon-default-storage_temperature"/>
                            </div>

                            @error('storage_temperature')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-storage_conditions">Условия
                                хранения <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-storage_conditions"
                                  class="input-group-text @error('storage_conditions') text-danger icon-border @enderror">
                                  <i class="bx bx-archive"></i>
                            </span>
                                <input type="text" id="basic-icon-default-storage_conditions"
                                       class="form-control @error('storage_conditions') is-invalid @enderror"
                                       name="storage_conditions"
                                       required
                                       value="{{ old('storage_conditions', $medication->storage->storage_conditions) }}"
                                       placeholder="Условия хранения"
                                       aria-label="Условия хранения"
                                       aria-describedby="basic-icon-default-storage_conditions"/>
                            </div>

                            @error('storage_conditions')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Navigation -->
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev">
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i> Назад
                            </button>
                            <button class="btn btn-primary btn-next">
                                Далее <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Лицензии и сертификация -->
                <div id="license" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Лицензии и сертификация</h6>
                    </div>
                    <div class="row g-3">
                        @php
                            $license = $medication->licenseAndCertification ?? null;
                        @endphp

                        @foreach ([
                            'sales_license' => 'Лицензия на продажу',
                            'quality_certificate' => 'Сертификат качества',
                            'clinical_trials' => 'Клинические исследования',
                            'registration_documents' => 'Регистрационные документы'
                        ] as $field => $label)
                            <div class="col-sm-6">
                                <label class="form-label" for="basic-icon-default-{{ $field }}">{{ $label }}</label>
                                <div class="input-group input-group-merge">
                                    <input type="file" id="basic-icon-default-{{ $field }}"
                                           class="form-control @error($field) is-invalid @enderror"
                                           name="{{ $field }}"
                                           aria-label="{{ $label }}"
                                           aria-describedby="basic-icon-default-{{ $field }}"/>

                                    @if ($license && $license->$field)
                                        <a href="{{ asset('storage/' . $license->$field) }}" target="_blank"
                                           class="btn btn-outline-primary">
                                            <i class="bx bx-download"></i>Cкачать
                                        </a>
                                    @endif
                                </div>

                                @error($field)
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach

                        <!-- Navigation -->
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev">
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i> Назад
                            </button>
                            <button class="btn btn-primary btn-next">
                                Далее <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                            </button>
                        </div>

                    </div>
                </div>

                <!-- Прочие данные -->
                <div id="data" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Прочие данные</h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-manufacturing_license_number"> Номер
                                лицензии на производство </label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-manufacturing_license_number"
                                  class="input-group-text @error('manufacturing_license_number') text-danger icon-border @enderror">
                                                <i class="bx bx-file"></i>
                            </span>
                                <input type="text" id="basic-icon-default-manufacturing_license_number"
                                       class="form-control @error('manufacturing_license_number') is-invalid @enderror"
                                       name="manufacturing_license_number"
                                       value="{{ old('manufacturing_license_number', $medication->additionalData->manufacturing_license_number) }}"
                                       placeholder="Номер лицензии на производство"
                                       aria-label="Номер лицензии на производство"
                                       aria-describedby="basic-icon-default-manufacturing_license_number"/>
                            </div>

                            @error('manufacturing_license_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-additional_usage_conditions">Дополнительные
                                условия использования </label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-additional_usage_conditions"
                                  class="input-group-text @error('additional_usage_conditions') text-danger icon-border @enderror">
                                  <i class="bx bx-list-check"></i>
                            </span>
                                <input type="text" id="basic-icon-default-additional_usage_conditions"
                                       class="form-control @error('additional_usage_conditions') is-invalid @enderror"
                                       name="additional_usage_conditions"
                                       value="{{ old('additional_usage_conditions', $medication->additionalData->additional_usage_conditions) }}"
                                       placeholder="Дополнительные условия использования"
                                       aria-label="Дополнительные условия использования"
                                       aria-describedby="basic-icon-default-additional_usage_conditions"/>
                            </div>

                            @error('additional_usage_conditions')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-prescription_category">Категория
                                рецепта </label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-prescription_category"
                                  class="input-group-text @error('prescription_category') text-danger icon-border @enderror">
                                 <i class='bx bx-category'></i>
                            </span>
                                <input type="text" id="basic-icon-default-prescription_category"
                                       class="form-control @error('prescription_category') is-invalid @enderror"
                                       name="prescription_category"
                                       value="{{ old('prescription_category', $medication->additionalData->prescription_category) }}"
                                       placeholder="Категория рецепта"
                                       aria-label="Категория рецепта"
                                       aria-describedby="basic-icon-default-prescription_category"/>
                            </div>

                            @error('prescription_category')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-specific_features">Индивидуальные
                                особенности </label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-specific_features"
                                  class="input-group-text @error('specific_features') text-danger icon-border @enderror">
                                               <i class="bx bx-bulb"></i>
                            </span>
                                <input type="text" id="basic-icon-default-specific_features"
                                       class="form-control @error('registration_specific_featuresdocuments') is-invalid @enderror"
                                       name="specific_features"
                                       value="{{ old('specific_features', $medication->additionalData->specific_features) }}"
                                       placeholder="Индивидуальные особенности"
                                       aria-label="Индивидуальные особенности"
                                       aria-describedby="basic-icon-default-specific_features"/>
                            </div>

                            @error('specific_features')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <!-- Navigation -->
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev"><i
                                    class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                                <span class="d-sm-inline-block d-none">Назад</span>
                            </button>
                            <button type="submit" class="btn btn-warning">Редактировать</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            // Initialize Flatpickr
            flatpickr('.flatpickr-date', {dateFormat: 'Y-m-d'});
            flatpickr('.flatpickr-time', {enableTime: true, noCalendar: true, time_24hr: true});
        });
    </script>
@endsection
