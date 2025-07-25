@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Препараты</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('medications.index') }}">Препараты </a> /</span>Создать
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
            <form method="post" action="{{ route('medications.store') }}" enctype="multipart/form-data">
                @csrf

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
                                       value="{{ old('name') }}"
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
                                        value="{{ $form }}" @selected($form == old('release_form'))>
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
                                       value="{{ old('dosage') }}"
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
                                        value="{{ $group->id }}" @selected($group->id == old('group_id'))>
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
                                        value="{{ $manufacturer->id }}" @selected($manufacturer->id == old('manufacturer_id'))>
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
                                       value="{{ old('registration_number') }}"
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
                                <input type="text" name="registration_date" value="{{ old('registration_date') }}"
                                       id="basic-icon-registration_date"
                                       class="form-control flatpickr-date @error('registration_date') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD" aria-describedby="basic-icon-birth-date2" required/>
                            </div>
                            @error('registration_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- new --}}
                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-min_deduction">Минимальное списание
                                <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('min_deduction') text-danger icon-border @enderror"><i
                                    class="bx bx-rename"></i></span>
                                <input type="text" id="basic-icon-default-min_deduction"
                                       class="form-control @error('min_deduction') is-invalid @enderror"
                                       placeholder="Минимальное списание" aria-label="Минимальное списание"
                                       name="min_deduction"
                                       value="{{ old('min_deduction') }}"
                                       aria-describedby="basic-icon-default-company2" required/>
                            </div>
                            @error('min_deduction')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-min_deduction">Минимальное количество
                                <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('min_quantity') text-danger icon-border @enderror"><i
                                    class="bx bx-rename"></i></span>
                                <input type="text" id="basic-icon-default-min_quantity"
                                       class="form-control @error('min_quantity') is-invalid @enderror"
                                       placeholder="Минимальное количество" aria-label="Минимальное количество"
                                       name="min_quantity"
                                       value="{{ old('min_quantity') }}"
                                       aria-describedby="basic-icon-default-company2" required/>
                            </div>
                            @error('min_quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-notify_before_days">Уведомить о сроке за сколько дней
                                <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('notify_before_days') text-danger icon-border @enderror"><i
                                    class="bx bx-rename"></i></span>
                                <input type="text" id="basic-icon-default-notify_before_days"
                                       class="form-control @error('notify_before_days') is-invalid @enderror"
                                       placeholder="Уведомить о сроке за сколько дней" aria-label="Уведомить о сроке за сколько дней"
                                       name="notify_before_days"
                                       value="{{ old('notify_before_days') }}"
                                       aria-describedby="basic-icon-default-company2" required/>
                            </div>
                            @error('notify_before_days')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-12">
                            <label class="form-label" for="basic-icon-default-notify_min_qty">Уведомить за какое минимальное количество
                                <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('notify_min_qty') text-danger icon-border @enderror"><i
                                    class="bx bx-rename"></i></span>
                                <input type="text" id="basic-icon-default-notify_min_qty"
                                       class="form-control @error('notify_min_qty') is-invalid @enderror"
                                       placeholder="Уведомить за какое минимальное количество" aria-label="Уведомить за какое минимальное количество"
                                       name="notify_min_qty"
                                       value="{{ old('notify_min_qty') }}"
                                       aria-describedby="basic-icon-default-company2" required/>
                            </div>
                            @error('notify_min_qty')
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
                                       value="{{ old('active_substance_name') }}"
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
                                       value="{{ old('active_substance_dosage') }}"
                                       placeholder="Дозировка активного вещества"
                                       aria-label="Дозировка активного вещества"
                                       required
                                       aria-describedby="basic-icon-default-active_substance_dosage"/>
                            </div>
                            @error('active_substance_dosage')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="additional-substance-wrapper">
                            <div class="row g-3 additional-substance-group mt-1 border p-2 rounded position-relative">
                                <button type="button"
                                        class="btn-close position-absolute top-0 end-0 m-2 remove-substance"
                                        aria-label="Удалить"></button>

                                <div class="col-sm-6">
                                    <label class="form-label">Вспомогательные вещества (Название) <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="bx bx-list-check"></i></span>
                                        <input type="text" name="additional_substance[0][substance_name]"
                                               class="form-control"
                                               placeholder="Название вспомогательного вещества" required>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <label class="form-label">Вспомогательные вещества (Дозировка) <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="bx bx-sort-alt-2"></i></span>
                                        <input type="text" name="additional_substance[0][substance_dosage]"
                                               class="form-control"
                                               placeholder="Дозировка вспомогательного вещества" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Кнопка добавления -->
                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-primary" id="add-additional-substance">
                                <i class="bx bx-plus"></i> Добавить вспомогательное вещество
                            </button>
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
                        <h6 class="mb-0">Применение</h6>
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
                                       value="{{ old('administration_method') }}"
                                       required
                                       placeholder="Способ применения"/>
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
                                       value="{{ old('dosage_regimen') }}"
                                       required
                                       placeholder="Режим дозирования"/>
                            </div>
                            @error('dosage_regimen')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-drug_interactions">Взаимодействие с
                                другими препаратами <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-drug_interactions"
                                      class="input-group-text @error('drug_interactions') text-danger icon-border @enderror">
                                    <i class="bx bx-git-merge"></i>
                                </span>
                                <input type="text" id="basic-icon-default-drug_interactions"
                                       class="form-control @error('drug_interactions') is-invalid @enderror"
                                       name="drug_interactions"
                                       value="{{ old('drug_interactions') }}"
                                       required
                                       placeholder="Взаимодействие с другими препаратами"/>
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
                                       value="{{ old('storage_temperature') }}"
                                       placeholder="Температурный режим хранения"/>
                            </div>
                            @error('storage_temperature')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="storage_conditions">Условия хранения<span
                                    class="text-danger">*</span></label>

                            <select id="storage_conditions" name="storage_conditions"
                                    class="form-control select2 @error('storage_conditions') is-invalid @enderror"
                                    required>
                                <option value="" disabled {{ old('storage_conditions') ? '' : 'selected' }}>Выберите
                                    условие хранения
                                </option>
                                <option
                                    value="Хранить в холодильнике (2–8°C)" {{ old('storage_conditions') == 'Хранить в холодильнике (2–8°C)' ? 'selected' : '' }}>
                                    Хранить в холодильнике (2–8°C)
                                </option>
                                <option
                                    value="Комнатная температура (15–25°C)" {{ old('storage_conditions') == 'Комнатная температура (15–25°C)' ? 'selected' : '' }}>
                                    Комнатная температура (15–25°C)
                                </option>
                                <option
                                    value="Хранить в защищённом от света месте" {{ old('storage_conditions') == 'Хранить в защищённом от света месте' ? 'selected' : '' }}>
                                    Хранить в защищённом от света месте
                                </option>
                                <option
                                    value="Заморозка (ниже 0°C)" {{ old('storage_conditions') == 'Заморозка (ниже 0°C)' ? 'selected' : '' }}>
                                    Заморозка (ниже 0°C)
                                </option>
                            </select>

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
                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-sales_license">Лицензия на
                                продажу </label>
                            <div class="input-group input-group-merge">
                                <input type="file" id="basic-icon-default-sales_license"
                                       class="form-control @error('sales_license') is-invalid @enderror"
                                       name="sales_license"
                                       value="{{ old('sales_license') }}"
                                       placeholder="Лицензия на продажу"
                                       aria-label="Лицензия на продажу"
                                       aria-describedby="basic-icon-default-sales_license"/>
                            </div>

                            @error('sales_license')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-quality_certificate">Сертификат
                                качества </label>
                            <div class="input-group input-group-merge">
                                <input type="file" id="basic-icon-default-quality_certificate"
                                       class="form-control @error('quality_certificate') is-invalid @enderror"
                                       name="quality_certificate"
                                       value="{{ old('quality_certificate') }}"
                                       placeholder="Сертификат качества"
                                       aria-label="Сертификат качества"
                                       aria-describedby="basic-icon-default-quality_certificate"/>
                            </div>

                            @error('quality_certificate')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-clinical_trials">Клинические
                                исследования </label>
                            <div class="input-group input-group-merge">
                                <input type="file" id="basic-icon-default-clinical_trials"
                                       class="form-control @error('clinical_trials') is-invalid @enderror"
                                       name="clinical_trials"
                                       value="{{ old('clinical_trials') }}"
                                       placeholder="Клинические исследования"
                                       aria-label="Клинические исследования"
                                       aria-describedby="basic-icon-default-clinical_trials"/>
                            </div>

                            @error('clinical_trials')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-registration_documents">Регистрационные
                                документы </label>
                            <div class="input-group input-group-merge">
                                <input type="file" id="basic-icon-default-registration_documents"
                                       class="form-control @error('registration_documents') is-invalid @enderror"
                                       name="registration_documents"
                                       value="{{ old('registration_documents') }}"
                                       placeholder="Регистрационные документы"
                                       aria-label="Регистрационные документы"
                                       aria-describedby="basic-icon-default-registration_documents"/>
                            </div>

                            @error('registration_documents')
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

                <!-- Прочие данные -->
                <div id="data" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Прочие данные</h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-manufacturing_license_number"> Номер
                                лицензии на производство</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-manufacturing_license_number"
                                      class="input-group-text @error('manufacturing_license_number') text-danger icon-border @enderror">
                                    <i class="bx bx-file"></i>
                                </span>
                                <input type="text" id="basic-icon-default-manufacturing_license_number"
                                       class="form-control @error('manufacturing_license_number') is-invalid @enderror"
                                       name="manufacturing_license_number"
                                       value="{{ old('manufacturing_license_number') }}"
                                       placeholder="Номер лицензии на производство"/>
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
                                       value="{{ old('additional_usage_conditions') }}"
                                       placeholder="Дополнительные условия использования"/>
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
                                       value="{{ old('prescription_category') }}"
                                       placeholder="Категория рецепта"/>
                            </div>
                            @error('prescription_category')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-specific_features">Индивидуальные
                                особенности</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-specific_features"
                                      class="input-group-text @error('specific_features') text-danger icon-border @enderror">
                                    <i class="bx bx-bulb"></i>
                                </span>
                                <input type="text" id="basic-icon-default-specific_features"
                                       class="form-control @error('specific_features') is-invalid @enderror"
                                       name="specific_features"
                                       value="{{ old('specific_features') }}"
                                       placeholder="Индивидуальные особенности"/>
                            </div>
                            @error('specific_features')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Navigation -->
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev">
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i> Назад
                            </button>
                            <button type="submit" class="btn btn-success">Сохранить</button>
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
            flatpickr('.flatpickr-date', {dateFormat: 'Y-m-d'});
            flatpickr('.flatpickr-time', {enableTime: true, noCalendar: true, time_24hr: true});

            let additionalSubstanceIndex = 1;

            $('#add-additional-substance').on('click', function () {
                const newGroup = `
        <div class="row g-3 additional-substance-group mt-1 border p-2 rounded position-relative">
            <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-substance" aria-label="Удалить"></button>

            <div class="col-sm-6">
                <label class="form-label">Вспомогательные вещества (Название) <span class="text-danger">*</span></label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-list-check"></i></span>
                    <input type="text" name="additional_substance[${additionalSubstanceIndex}][substance_name]"
                           class="form-control" placeholder="Название вспомогательного вещества" required>
                </div>
            </div>

            <div class="col-sm-6">
                <label class="form-label">Вспомогательные вещества (Дозировка) <span class="text-danger">*</span></label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-sort-alt-2"></i></span>
                    <input type="text" name="additional_substance[${additionalSubstanceIndex}][substance_dosage]"
                           class="form-control" placeholder="Дозировка вспомогательного вещества" required>
                </div>
            </div>
        </div>
    `;

                $('#additional-substance-wrapper').append(newGroup);
                additionalSubstanceIndex++;
            });

            $(document).on('click', '.remove-substance', function () {
                $(this).closest('.additional-substance-group').remove();
            });
        });
    </script>
@endsection
