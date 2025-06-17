@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Редактировать назначиную услугу</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('patient.services.index') }}">Услуги </a> /</span>Редактировать
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
            <div class="step" data-target="#details">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">2</span>
                    <span class="bs-stepper-label">Детали</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#procedures">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">3</span>
                    <span class="bs-stepper-label">Процедуры</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#medicaments">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">4</span>
                    <span class="bs-stepper-label">Препараты</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#additional_data">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">5</span>
                    <span class="bs-stepper-label">Финансовая информация</span>
                </button>
            </div>
        </div>

        <div class="bs-stepper-content">
            <form method="post" action="{{ route('patient.services.update',  $service->id) }}"
                  enctype="multipart/form-data">
                @csrf
                @method('put')

                <!-- Основные данные -->
                <div id="basic-data" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Основные данные</h6>
                    </div>
                    <div class="row g-3">
                        <!-- Patient -->
                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-patient">Пациент <span
                                    class="text-danger">*</span></label>
                            <select id="basic-icon-default-patient" name="patient_id"
                                    class="form-control select2 @error('patient_id') is-invalid @enderror" required>
                                <option value="" disabled selected>Выберите пациента</option>
                                @foreach($patients as $patient)
                                    <option
                                        value="{{ $patient->id }}" @selected(old('patient_id', $service->patient_id) == $patient->id)>
                                        {{ $patient->first_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                            <div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Examination Map -->
                        <div class="col-sm-4">
                            <label class="form-label" for="patient-map">Карта обследования <span
                                    class="text-danger">*</span></label>
                            <select id="patient-map" name="examination_map_id"
                                    class="form-control select2 @error('examination_map_id') is-invalid @enderror"
                                    required>
                                @foreach($maps as $map)
                                    <option value="{{$map->id}}" @selected($map->id == $service->examination_map_id)>
                                        {{ $map->patient->first_name }} — {{ $map->admission_date }}
                                    </option>
                                @endforeach
                            </select>
                            @error('examination_map_id')
                            <div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Service -->
                        <div class="col-sm-4">
                            <label class="form-label" for="service_id">Услуга <span class="text-danger">*</span></label>
                            <select id="service_id" name="service_id"
                                    class="form-control select2 @error('service_id') is-invalid @enderror" required>
                                <option value="" disabled selected>Выберите услугу</option>
                                @foreach($maintenances as $maintanance)
                                    <option
                                        value="{{ $maintanance->id }}" @selected(old('service_id', $service->service_id) == $maintanance->id)>
                                        {{ $maintanance->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_id')
                            <div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Place -->
                        <div class="col-sm-12">
                            <label class="form-label" for="place">Место оказания <span
                                    class="text-danger">*</span></label>
                            <select id="place" name="place"
                                    class="form-control select2 @error('place') is-invalid @enderror" required>
                                @foreach(\App\Models\PatientService::PLACE as $value)
                                    <option value="{{ $value }}" @selected(old('place' ,$service->place) == $value)>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('place')
                            <div class="invalid-feedback">{{ $message }}</div>@enderror
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

                <!-- Детали -->
                <div id="details" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Детали</h6>
                    </div>
                    <div class="row g-3">
                        {{-- status --}}
                        <input type="hidden" name="status" value="assigned">

                        <!-- Service Date -->
                        <div class="col-sm-4">
                            <label class="form-label" for="service_date">Дата услуги <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                               <span id="basic-icon-birth-date2"
                                     class="input-group-text @error('birth_date') text-danger icon-border @enderror">
                                    <i class="bx bx-calendar"></i>
                               </span>
                                <input type="text" name="service_date" id="service_date"
                                       class="form-control flatpickr-date @error('service_date') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD"
                                       value="{{ old('service_date', $service->service_date) }}" required>
                            </div>

                            @error('service_date')
                            <div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Service Time -->
                        <div class="col-sm-4">
                            <label class="form-label" for="service_time">Время услуги</label>
                            <input type="text"
                                   name="service_time"
                                   id="service_time"
                                   class="flatpickr-time form-control @error('service_time') is-invalid @enderror"
                                   value="{{ old('service_time', $service->service_time) }}">
                            @error('service_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Methods -->
                        <div class="col-sm-4">
                            <label class="form-label" for="methods">Методики</label>
                            <select id="methods" name="methods[]"
                                    class="form-control select2 @error('methods') is-invalid @enderror" multiple>
                                @foreach($methods as $method)
                                    <option
                                        value="{{ $method->id }}" @selected(in_array($method->id, old('methods', $service->methods->pluck('id')->toArray())))>
                                        {{ $method->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('methods')
                            <div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Equipments -->
                        <div class="col-sm-6">
                            <label class="form-label" for="equipments">Оборудование</label>
                            <select id="equipments" name="equipments[]"
                                    class="form-control select2 @error('equipments') is-invalid @enderror" multiple>
                                @foreach($equipments as $equipment)
                                    <option
                                        value="{{ $equipment->id }}" @selected(in_array($equipment->id, old('equipments', $service->equipments->pluck('id')->toArray())))>
                                        {{ $equipment->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('equipments')
                            <div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Staffs -->
                        <div class="col-sm-6">
                            <label class="form-label" for="staffs">Персонал <span class="text-danger">*</span></label>
                            <select id="staffs" name="staffs[]"
                                    class="form-control select2 @error('staffs') is-invalid @enderror" multiple
                                    required>
                                @foreach($staffs as $staff)
                                    <option
                                        value="{{ $staff->id }}" @selected(in_array($staff->id, old('staffs', $service->staffs->pluck('id')->toArray())))>
                                        {{ $staff->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('staffs')
                            <div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Results -->
                        <div class="col-12">
                            <label class="form-label" for="result">Результаты</label>
                            <textarea id="result" name="result"
                                      class="form-control @error('result') is-invalid @enderror"
                                      rows="4">{{ old('result', $service->result) }}</textarea>
                            @error('result')
                            <div class="invalid-feedback">{{ $message }}</div>@enderror
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

                <!-- Процедуры -->
                <div id="procedures" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Процедуры</h6>
                    </div>
                    <div class="row g-3">
                        <div class="mb-3">
                            <label class="form-label">Процедуры</label>
                            <div id="procedures-container">
                                @foreach($service->procedures as $index => $procedure)
                                    <div class="row mb-2 procedure-row">
                                        <div class="col-sm-5">
                                            <select name="procedures[]" class="form-control select2"
                                                    data-placeholder="Выберите или введите новую процедуру" required>
                                                <option value="{{ $procedure->id }}">{{ $procedure->name }}</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-5">
                                            <input type="date" name="procedure_dates[]"
                                                   class="form-control"
                                                   value="{{ old('procedure_dates.'.$index, now()->format('Y-m-d')) }}"
                                                   required>
                                        </div>
                                        <div class="col-sm-2">
                                            <button type="button" class="btn btn-danger btn-remove-procedure">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" class="btn btn-primary mt-2" id="btn-add-procedure">
                                <i class="fas fa-plus me-2"></i> Добавить процедуру
                            </button>

                            <template id="procedure-template">
                                <div class="row mb-2 procedure-row">
                                    <div class="col-sm-5">
                                        <select name="procedures[]" class="form-control procedure-select"
                                                data-placeholder="Выберите или введите новую процедуру" required>
                                        </select>
                                    </div>
                                    <div class="col-sm-5">
                                        <input type="date" name="procedure_dates[]"
                                               class="form-control"
                                               value="{{ now()->format('Y-m-d') }}"
                                               required>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="button" class="btn btn-danger btn-remove-procedure">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
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

                <!-- Препараты -->
                <div id="medicaments" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Препараты</h6>
                    </div>
                    <div class="row g-3">
                        <div class="mb-3">
                            <label class="form-label">Препараты</label>
                            <div id="medications-container">
                                @foreach($service->medications as $index => $medication)
                                    <div class="row mb-2 medication-row">
                                        <div class="col-sm-4">
                                            <select name="medications[]" class="form-control medication-select select2"
                                                    required>
                                                @foreach($medications as $medicaton)
                                                    <option value="{{ $medicaton->id }}" @selected($medicaton->id == $medication->id)>{{ $medicaton->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-4">
                                            <input type="text" name="dosages[]"
                                                   class="form-control"
                                                   value="{{$medication->pivot->dosage}}"
                                                   placeholder="Дозировка"
                                                   required>
                                        </div>
                                        <div class="col-sm-4">
                                            <input type="text" name="medication_notes[]"
                                                   class="form-control"
                                                   value="{{$medication->pivot->notes}}"
                                                   placeholder="Примечания"></input>
                                        </div>

                                        <div class="col-12 mb-3 mt-3">
                                            <label class="form-label fw-semibold">Период приёма</label>
                                            <div class="admission-period-wrapper border rounded p-2">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <select class="form-select w-auto admission-hour">
                                                        @for($h = 0; $h < 24; $h++)
                                                            <option
                                                                value="{{ sprintf('%02d', $h) }}">{{ sprintf('%02d', $h) }}</option>
                                                        @endfor
                                                    </select>
                                                    <span>:</span>
                                                    <select class="form-select w-auto admission-minute">
                                                        @for($m = 0; $m < 60; $m += 5)
                                                            <option
                                                                value="{{ sprintf('%02d', $m) }}">{{ sprintf('%02d', $m) }}</option>
                                                        @endfor
                                                    </select>
                                                    <button type="button"
                                                            class="btn btn-outline-success btn-add-time"
                                                            title="Добавить время">
                                                        <i class="fas fa-clock"></i>
                                                    </button>
                                                </div>

                                                <div class="admission-times-list d-flex flex-wrap gap-2"></div>

                                                <input type="hidden" name="admission_periods[]" class="admission-hidden-input"
                                                       value="{{ $medication->pivot->admission_period }}"
                                                       data-times="{{ $medication->pivot->admission_period }}">
                                            </div>
                                        </div>

                                        <div class="col-sm-3">
                                            <button type="button" class="btn btn-danger btn-remove-medication">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" class="btn btn-primary mt-2" id="btn-add-medication">
                                <i class="fas fa-plus me-2"></i> Добавить препарат
                            </button>

                            <template id="medication-template">
                                <div class="row mb-2 medication-row">
                                    <div class="col-sm-4">
                                        <select name="medications[]" class="form-control medication-select"
                                                required>
                                            <option selected disabled>Выберите медикамент</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="text" name="dosages[]"
                                               class="form-control"
                                               placeholder="Дозировка"
                                               required>
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="text" name="medication_notes[]"
                                               class="form-control"
                                               placeholder="Примечания"></input>
                                    </div>


                                    <div class="col-12 mb-3 mt-3">
                                        <label class="form-label fw-semibold">Период приёма</label>
                                        <div class="admission-period-wrapper border rounded p-2">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <select class="form-select w-auto admission-hour">
                                                    @for($h = 0; $h < 24; $h++)
                                                        <option
                                                            value="{{ sprintf('%02d', $h) }}">{{ sprintf('%02d', $h) }}</option>
                                                    @endfor
                                                </select>
                                                <span>:</span>
                                                <select class="form-select w-auto admission-minute">
                                                    @for($m = 0; $m < 60; $m += 5)
                                                        <option
                                                            value="{{ sprintf('%02d', $m) }}">{{ sprintf('%02d', $m) }}</option>
                                                    @endfor
                                                </select>
                                                <button type="button" class="btn btn-outline-success btn-add-time"
                                                        title="Добавить время">
                                                    <i class="fas fa-clock"></i>
                                                </button>
                                            </div>

                                            <div class="admission-times-list d-flex flex-wrap gap-2"></div>

                                            <input type="hidden" name="admission_periods[]"
                                                   class="admission-hidden-input">
                                        </div>
                                    </div>

                                    <div class="col-sm-3">
                                        <button type="button" class="btn btn-danger btn-remove-medication">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
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

                <!-- Финансовая информация -->
                <div id="additional_data" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Финансовая информация</h6>
                    </div>
                    <div class="row g-3">
                        <!-- Payment Status -->
                        @if(isset($service->payment['status']))
                            <div class="col-sm-6">
                                <label class="form-label" for="payment_status">Статус оплаты</label>
                                <select id="payment_status" name="payment[status]"
                                        class="form-control select2 @error('payment.status') is-invalid @enderror">
                                    <option value="" selected>Не выбрано</option>
                                    @foreach(\App\Models\PatientService::PAYMENT_STATUS as $status)
                                        <option
                                            value="{{ $status }}" @selected(old('payment.status', $service->payment['status']) == $status)>
                                            {{ $status }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment.status')
                                <div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @else
                            <div class="col-sm-6">
                                <label class="form-label" for="payment_status">Статус оплаты</label>
                                <select id="payment_status" name="payment[status]"
                                        class="form-control select2 @error('payment.status') is-invalid @enderror">
                                    <option value="" selected>Не выбрано</option>
                                    @foreach(\App\Models\PatientService::PAYMENT_STATUS as $status)
                                        <option
                                            value="{{ $status }}" @selected(old('payment.status') == $status)>
                                            {{ $status }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment.status')
                                <div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @endif

                        @if(isset($service->payment['method']))
                            <!-- Payment Method -->
                            <div class="col-sm-6">
                                <label class="form-label" for="payment_method">Метод оплаты</label>
                                <select id="payment_method" name="payment[method]"
                                        class="form-control select2 @error('payment.method') is-invalid @enderror">
                                    <option value="" selected>Не выбрано</option>
                                    @foreach(\App\Models\PatientService::PAYMENT_METHOD as $method)
                                        <option
                                            value="{{ $method }}" @selected(old('payment.method', $service->payment['method']) == $method)>
                                            {{ $method }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment.method')
                                <div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @else
                            <!-- Payment Method -->
                            <div class="col-sm-6">
                                <label class="form-label" for="payment_method">Метод оплаты</label>
                                <select id="payment_method" name="payment[method]"
                                        class="form-control select2 @error('payment.method') is-invalid @enderror">
                                    <option value="" selected>Не выбрано</option>
                                    @foreach(\App\Models\PatientService::PAYMENT_METHOD as $method)
                                        <option
                                            value="{{ $method }}" @selected(old('payment.method') == $method)>
                                            {{ $method }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment.method')
                                <div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @endif

                        @if(isset($service->insurance_policy['company_name']))
                            <!-- Insurance -->
                            <div class="col-sm-4">
                                <label class="form-label" for="insurance_company">Страховая компания</label>
                                <div class="input-group input-group-merge">
                                <span
                                    class="input-group-text @error('insurance_policy.company_name') text-danger icon-border @enderror">
                                    <i class="bx bx-building"></i>
                                </span>
                                    <input type="text" id="insurance_company" name="insurance_policy[company_name]"
                                           class="form-control @error('insurance_policy.company_name') is-invalid @enderror"
                                           value="{{ old('insurance_policy.company_name', $service->insurance_policy['company_name']) }}">
                                </div>
                                @error('insurance_policy.company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @else
                            <!-- Insurance -->
                            <div class="col-sm-4">
                                <label class="form-label" for="insurance_company">Страховая компания</label>
                                <div class="input-group input-group-merge">
                                <span
                                    class="input-group-text @error('insurance_policy.company_name') text-danger icon-border @enderror">
                                    <i class="bx bx-building"></i>
                                </span>
                                    <input type="text" id="insurance_company" name="insurance_policy[company_name]"
                                           class="form-control @error('insurance_policy.company_name') is-invalid @enderror"
                                           value="{{ old('insurance_policy.company_name') }}">
                                </div>
                                @error('insurance_policy.company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        @if(isset($service->insurance_policy['insurance_policy_number']))
                            <div class="col-sm-4">
                                <label class="form-label" for="policy_number">Номер полиса</label>
                                <div class="input-group input-group-merge">
                                <span
                                    class="input-group-text @error('insurance_policy.insurance_policy_number') text-danger icon-border @enderror">
                                    <i class="bx bx-id-card"></i>
                                </span>
                                    <input type="text" id="policy_number"
                                           name="insurance_policy[insurance_policy_number]"
                                           class="form-control @error('insurance_policy.insurance_policy_number') is-invalid @enderror"
                                           value="{{ old('insurance_policy.insurance_policy_number', $service->insurance_policy['insurance_policy_number']) }}">
                                </div>
                                @error('insurance_policy.insurance_policy_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @else
                            <div class="col-sm-4">
                                <label class="form-label" for="policy_number">Номер полиса</label>
                                <div class="input-group input-group-merge">
                                <span
                                    class="input-group-text @error('insurance_policy.insurance_policy_number') text-danger icon-border @enderror">
                                    <i class="bx bx-id-card"></i>
                                </span>
                                    <input type="text" id="policy_number"
                                           name="insurance_policy[insurance_policy_number]"
                                           class="form-control @error('insurance_policy.insurance_policy_number') is-invalid @enderror"
                                           value="{{ old('insurance_policy.insurance_policy_number') }}">
                                </div>
                                @error('insurance_policy.insurance_policy_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        @if(isset($service->insurance_policy['insurance_policy_number']))
                            <div class="col-sm-4">
                                <label class="form-label" for="coverage">Процент покрытия</label>
                                <div class="input-group input-group-merge">
                                <span
                                    class="input-group-text @error('insurance_policy.percentage_of_coverage') text-danger icon-border @enderror">
                                    <i class="bx bx-percent"></i>
                                </span>
                                    <input type="number" id="coverage" name="insurance_policy[percentage_of_coverage]"
                                           class="form-control @error('insurance_policy.percentage_of_coverage') is-invalid @enderror"
                                           value="{{ old('insurance_policy.percentage_of_coverage', $service->insurance_policy['percentage_of_coverage']) }}">
                                </div>
                                @error('insurance_policy.percentage_of_coverage')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @else
                            <div class="col-sm-4">
                                <label class="form-label" for="coverage">Процент покрытия</label>
                                <div class="input-group input-group-merge">
                                <span
                                    class="input-group-text @error('insurance_policy.percentage_of_coverage') text-danger icon-border @enderror">
                                    <i class="bx bx-percent"></i>
                                </span>
                                    <input type="number" id="coverage" name="insurance_policy[percentage_of_coverage]"
                                           class="form-control @error('insurance_policy.percentage_of_coverage') is-invalid @enderror"
                                           value="{{ old('insurance_policy.percentage_of_coverage') }}">
                                </div>
                                @error('insurance_policy.percentage_of_coverage')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif


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
            // Load Examination Maps
            $('#basic-icon-default-patient').on('change', function () {
                const patientId = $(this).val();
                const mapSelect = $('#patient-map');

                if (!patientId) {
                    mapSelect.html('<option value="" disabled selected>Сначала выберите пациента</option>');
                    return;
                }

                mapSelect.html('<option value="" disabled selected>Загрузка...</option>');

                $.ajax({
                    url: `/api/examination-map-by-patient/${patientId}`,
                    type: 'GET',
                    success: function (data) {
                        mapSelect.html('<option value="" disabled selected>Выберите карту</option>');
                        data.forEach(map => {
                            mapSelect.append(
                                `<option value="${map.id}">${map.patient_name} — ${map.admission_date}</option>`
                            );
                        });
                        mapSelect.val(null).trigger('change');
                    },
                    error: function () {
                        mapSelect.html('<option value="" disabled selected>Ошибка загрузки</option>');
                    }
                });
            });

            // Initialize Flatpickr
            flatpickr('.flatpickr-date', {dateFormat: 'Y-m-d'});
            flatpickr('.flatpickr-time', {enableTime: true, noCalendar: true});


            // procedures
            const container = document.getElementById('procedures-container');
            const template = document.getElementById('procedure-template');

            function initSelect2(element) {
                const procedureData = {!! json_encode($procedures->map(function($procedure) {
                    return ['id' => $procedure->id, 'text' => $procedure->name];
                })) !!};

                $(element).select2({
                    tags: true,
                    placeholder: $(element).data('placeholder'),
                    data: procedureData,
                    createTag: function (params) {
                        return {
                            id: params.term,
                            text: params.term + ' (новая)'
                        };
                    }
                });
            }

            document.getElementById('btn-add-procedure').addEventListener('click', function () {
                const clone = template.content.cloneNode(true);
                const select = clone.querySelector('.procedure-select');
                container.appendChild(clone);
                initSelect2(select);
            });

            container.addEventListener('click', function (e) {
                if (e.target.closest('.btn-remove-procedure')) {
                    e.target.closest('.procedure-row').remove();
                }
            });

            // medications
            const containerMedicaments = document.getElementById('medications-container');
            const templateMedicaments = document.getElementById('medication-template');

            $('.medication-select').each(function () {
                initMedicationSelect(this);
            });

            $('.admission-hidden-input').each(function () {
                const $input = $(this);
                const $wrapper = $input.closest('.admission-period-wrapper');
                const $list = $wrapper.find('.admission-times-list');

                const times = ($input.data('times') || '').split(',');
                times.forEach(function (time) {
                    if (!time) return;
                    const $chip = $(`
                <span class="badge bg-secondary d-flex align-items-center gap-1" data-time="${time}">
                    ${time}
                    <button type="button" class="btn-close btn-close-white btn-sm ms-1" aria-label="Удалить"></button>
                </span>
            `);
                    $list.append($chip);
                });
            });

            $(containerMedicaments).on('click', '.btn-add-time', function () {
                const $wrapper = $(this).closest('.admission-period-wrapper');
                const hour = $wrapper.find('.admission-hour').val();
                const minute = $wrapper.find('.admission-minute').val();
                const time = `${hour}:${minute}`;

                const $timesList = $wrapper.find('.admission-times-list');

                // Если уже есть такое время — не добавлять
                if ($timesList.find(`[data-time="${time}"]`).length > 0) return;

                const $chip = $(`
                    <span class="badge bg-secondary d-flex align-items-center gap-1" data-time="${time}">
                        ${time}
                        <button type="button" class="btn-close btn-close-white btn-sm ms-1" aria-label="Удалить"></button>
                    </span>
                `);

                $timesList.append($chip);

                updateHiddenAdmissionInput($wrapper);
            });

            // Удаление времени
            $(containerMedicaments).on('click', '.admission-times-list .btn-close', function () {
                const $wrapper = $(this).closest('.admission-period-wrapper');
                $(this).closest('[data-time]').remove();
                updateHiddenAdmissionInput($wrapper);
            });

            // Обновление скрытого input
            function updateHiddenAdmissionInput($wrapper) {
                const times = [];
                $wrapper.find('.admission-times-list [data-time]').each(function () {
                    times.push($(this).data('time'));
                });

                $wrapper.find('.admission-hidden-input').val(times.join(','));
            }


            function initMedicationSelect(element) {
                $(element).select2({
                    tags: true,
                    placeholder: 'Выберите медикамент',
                    data: {!! json_encode($medications->map(fn($m) => ['id' => $m->id, 'text' => $m->name])) !!},
                    createTag: function (params) {
                        return {
                            id: params.term,
                            text: params.term + ' (новый)'
                        };
                    }
                });

                bindMedicationChange(element);
            }


            function bindMedicationChange(select) {
                $(select).on('select2:select', function (e) {
                    const selectedId = e.params.data.id;
                    const $row = $(this).closest('.medication-row');
                    const $dosageInput = $row.find('input[name="dosages[]"]');

                    // Если это не число, значит пользователь ввёл новый медикамент
                    if (isNaN(selectedId)) {
                        $dosageInput.val('');
                        return;
                    }

                    fetch(`/api/medication/show/${selectedId}`)
                        .then(response => response.json())
                        .then(data => {
                            $dosageInput.val(data.dosage ?? '');
                        })
                        .catch(() => {
                            $dosageInput.val('');
                        });
                });
            }

            // Добавление новой строки
            $('#btn-add-medication').click(function () {
                const clone = templateMedicaments.content.cloneNode(true);
                const select = clone.querySelector('.medication-select');
                containerMedicaments.appendChild(clone);
                initMedicationSelect(select);
            });

            // Удаление строки
            $(containerMedicaments).on('click', '.btn-remove-medication', function () {
                $(this).closest('.medication-row').remove();
            });

        });
    </script>
@endsection
