@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Создать карту обследования</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">/</span>Создать
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
            <div class="step" data-target="#contact-info">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">2</span>
                    <span class="bs-stepper-label">Госпитализация</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#anamnesises">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">3</span>
                    <span class="bs-stepper-label">Анамнез</span>
                </button>
            </div>
            {{--            <div class="line"></div>--}}
            {{--            <div class="step" data-target="#analyzes">--}}
            {{--                <button type="button" class="step-trigger">--}}
            {{--                    <span class="bs-stepper-circle">3</span>--}}
            {{--                    <span class="bs-stepper-label">Анализ</span>--}}
            {{--                </button>--}}
            {{--            </div>--}}

            {{--            <div class="line"></div>--}}
            {{--            <div class="step" data-target="#service">--}}
            {{--                <button type="button" class="step-trigger">--}}
            {{--                    <span class="bs-stepper-circle">4</span>--}}
            {{--                    <span class="bs-stepper-label">Услуги</span>--}}
            {{--                </button>--}}
            {{--            </div>--}}
        </div>

        <div class="bs-stepper-content">
            <form method="post" action="{{ route('maps.store') }}"
                  enctype="multipart/form-data">
                @csrf
                <!-- Основные данные -->
                <div id="basic-data" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Основные данные</h6>
                    </div>
                    <div class="row g-3">
                        {{--                        <div class="col-sm-4">--}}
                        {{--                            <label class="form-label" for="basic-icon-default-patient">Пациент <span--}}
                        {{--                                    class="text-danger">*</span></label>--}}
                        {{--                            <select id="basic-icon-default-patient" name="patient_id"--}}
                        {{--                                    class="form-control select2 @error('patient_id') is-invalid @enderror" required>--}}
                        {{--                                <option value="" disabled>Выберите пациента</option>--}}
                        {{--                                @foreach($patients as $patient)--}}
                        {{--                                    <option--}}
                        {{--                                        value="{{ $patient->id }}" @selected($patient->id == old('patient_id'))>{{ $patient->first_name }}--}}
                        {{--                                        - {{  ($patient->id) }}</option>--}}
                        {{--                                @endforeach--}}
                        {{--                            </select>--}}
                        {{--                            @error('patient_id')--}}
                        {{--                            <div class="invalid-feedback">{{ $message }}</div>--}}
                        {{--                            @enderror--}}
                        {{--                        </div>--}}

                        <input type="hidden" name="patient_id" value="{{ $patient->id }}">

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-doctors">Лечащие врачи <span
                                    class="text-danger">*</span></label>
                            <select id="basic-icon-default-doctors" name="staff_id"
                                    class="form-control select2 @error('staff_id') is-invalid @enderror" required>
                                <option value="" disabled>Выберите врачей</option>
                                @foreach($doctors as $doctor)
                                    <option
                                        value="{{ $doctor->id }}" @selected($doctor->id == old('staff_id'))>{{ $doctor->name }}</option>
                                @endforeach
                            </select>
                            @error('staff_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-department">Отделение, к которому
                                прикреплен <span class="text-danger">*</span></label>
                            <select id="basic-icon-default-department" name="department_id"
                                    class="form-control select2 @error('department_id') is-invalid @enderror"
                                    required>
                                <option value="" disabled>Выберите отделение</option>
                                @foreach($institutions as $institution)
                                    <option
                                        value="{{ $institution->id }}" @selected($institution->id == old('department_id'))>{{ $institution->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-label-secondary btn-prev" disabled>
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                                <span class="d-sm-inline-block d-none">Назад</span>
                            </button>
                            <button class="btn btn-primary btn-next">
                                <span class="d-sm-inline-block d-none me-sm-1">Далее</span>
                                <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Госпитализация и Выписка --}}
                <div id="contact-info" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Госпитализация</h6>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-admission_date">Дата госпитализации <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-birth-date2"
                                      class="input-group-text @error('admission_date') text-danger icon-border @enderror">
                                    <i class="bx bx-calendar"></i>
                                </span>
                                <input type="text" name="admission_date" value="{{ old('admission_date') }}"
                                       id="basic-icon-admission_date"
                                       required
                                       class="form-control flatpickr-date @error('admission_date') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD" aria-describedby="basic-icon-birth-date2"/>
                            </div>
                            @error('admission_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-admission_diagnosis_code">Код по
                                МКБ-10 </label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-first-name2"
                                      class="input-group-text @error('admission_diagnosis_code') text-danger icon-border @enderror">
                                    <i class="bx bx-code"></i>
                                </span>
                                <input type="text" id="basic-icon-default-admission_diagnosis_code"
                                       class="form-control @error('admission_diagnosis_code') is-invalid @enderror"
                                       placeholder="код по МКБ-10 " aria-label="код по МКБ-10 "
                                       name="admission_diagnosis_code"
                                       value="{{ old('admission_diagnosis_code') }}"
                                       aria-describedby="basic-icon-default-first-name2"/>
                            </div>
                            @error('admission_diagnosis_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <div>
                            <label class="form-label" for="basic-icon-default-admission_diagnosis_description">Диагноз
                                при госпитализации </label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-history2"
                                      class="input-group-text @error('admission_diagnosis_description') text-danger icon-border @enderror">
                                    <i class="bx bx-notepad"></i>
                                </span>
                                <textarea id="basic-icon-default-admission_diagnosis_description"
                                          class="form-control @error('admission_diagnosis_description') is-invalid @enderror"
                                          placeholder="Диагноз при госпитализации"
                                          aria-label="Диагноз при госпитализации"
                                          name="admission_diagnosis_description"
                                          aria-describedby="basic-icon-default-history2">{{ old('admission_diagnosis_description') }}</textarea>
                            </div>
                            @error('admission_diagnosis_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="content-header mb-3 mt-3">
                        <h6 class="mb-0">Цель</h6>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="basic-icon-default-examination_purpose">Цель
                                обследования </label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-history2"
                                      class="input-group-text @error('examination_purpose') text-danger icon-border @enderror">
                                    <i class="bx bx-notepad"></i>
                                </span>
                                <textarea id="basic-icon-default-examination_purpose"
                                          class="form-control @error('examination_purpose') is-invalid @enderror"
                                          placeholder="Цель обследования"
                                          aria-label="Цель обследования"
                                          name="examination_purpose"
                                          aria-describedby="basic-icon-default-history2">{{ old('examination_purpose') }}</textarea>
                            </div>
                            @error('examination_purpose')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev">
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                                <span class="d-sm-inline-block d-none">Назад</span>
                            </button>
                            <button class="btn btn-primary btn-next"><span class="d-sm-inline-block d-none">Далее</span>
                                <i
                                    class="bx bx-chevron-right bx-sm me-sm-n2"></i></button>
                        </div>
                    </div>
                </div>

                {{-- Анамнез --}}
                <div id="anamnesises" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Анамнез</h6>
                    </div>

                    <div class="row g-3">
                        <div>
                            <label class="form-label" for="basic-icon-default-complaints">Жалобы пациента</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-history2"
                                      class="input-group-text @error('complaints') text-danger icon-border @enderror">
                                    <i class="bx bx-notepad"></i>
                                </span>
                                <textarea id="basic-icon-default-complaints"
                                          class="form-control @error('complaints') is-invalid @enderror"
                                          placeholder="Жалобы пациента"
                                          aria-label="Жалобы пациента"
                                          name="complaints"
                                          aria-describedby="basic-icon-default-history2">{{ old('complaints') }}</textarea>
                            </div>
                            @error('complaints')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label" for="basic-icon-default-disease_history">История настоящего
                                заболевания </label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-history2"
                                      class="input-group-text @error('disease_history') text-danger icon-border @enderror">
                                    <i class="bx bx-notepad"></i>
                                </span>
                                <textarea id="basic-icon-default-disease_history"
                                          class="form-control @error('disease_history') is-invalid @enderror"
                                          placeholder="История настоящего заболевания"
                                          aria-label="История настоящего заболевания"
                                          name="disease_history"
                                          aria-describedby="basic-icon-default-history2">{{ old('disease_history') }}</textarea>
                            </div>
                            @error('disease_history')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label" for="basic-icon-default-general_anamnesises">Общий
                                анамнез </label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-history2"
                                      class="input-group-text @error('general_anamnesises') text-danger icon-border @enderror">
                                    <i class="bx bx-notepad"></i>
                                </span>
                                <textarea id="basic-icon-default-general_anamnesises"
                                          class="form-control @error('general_anamnesises') is-invalid @enderror"
                                          placeholder="Общий анамнез"
                                          aria-label="Общий анамнез"
                                          name="general_anamnesises"
                                          aria-describedby="basic-icon-default-history2">{{ old('general_anamnesises') }}</textarea>
                            </div>
                            @error('general_anamnesises')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        @php
                            $firstDocExist = $patient->documentResponse()->where('document_id', 1)->where('examination_map_id', null)->exists()
                        @endphp
                        <div>
                            <a href="{{ route('documents.show', ['document' => 1, 'patient_id' => $patient->id]) }}"
                               class="btn btn-success @if($firstDocExist) disabled @endif"
                               style="@if($firstDocExist) pointer-events: none; @endif"
                               target="_blank">Первичный сестринский
                                осмотр</a>

                            @if($firstDocExist)
                                <a href="{{ route('documents.show.answer', $patient->documentResponse()->where('document_id', 1)->where('examination_map_id', null)->latest()->first()->id) }}"
                                   class="btn btn-success"
                                   target="_blank">Смотреть</a>

                                <a href="{{ route('documents.show.answer.delete', $patient->documentResponse()->where('document_id', 1)->where('examination_map_id', null)->latest()->first()->id) }}"
                                   class="btn btn-danger text-white">
                                    Удалить
                                </a>
                            @endif
                        </div>

                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev"><i
                                    class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                                <span class="d-sm-inline-block d-none">Назад</span>
                            </button>
                            <button type="submit" class="btn btn-success">Сохранить</button>
                        </div>
                    </div>
                </div>

                {{-- Анализ --}}
                {{--                <div id="analyzes" class="content">--}}
                {{--                    <div class="content-header mb-3">--}}
                {{--                        <h6 class="mb-0">Анализ</h6>--}}
                {{--                    </div>--}}

                {{--                    <div class="row g-3">--}}
                {{--                        <div class="col-sm-4">--}}
                {{--                            <label class="form-label" for="basic-icon-default-departments">Лаборатории </label>--}}
                {{--                            <select id="basic-icon-default-departments" name="laboratory_id"--}}
                {{--                                    class="form-control select2 @error('laboratory_id') is-invalid @enderror">--}}
                {{--                                <option value="" selected disabled>Выберите лабораторию</option>--}}
                {{--                                @foreach($laboratories as $laboratory)--}}
                {{--                                    <option--}}
                {{--                                        value="{{ $laboratory->id }}" @selected($laboratory->id == old('laboratory_id'))>{{ $laboratory->name }}</option>--}}
                {{--                                @endforeach--}}
                {{--                            </select>--}}
                {{--                            @error('laboratory_id')--}}
                {{--                            <div class="invalid-feedback">{{ $message }}</div>--}}
                {{--                            @enderror--}}
                {{--                        </div>--}}

                {{--                        <div class="col-sm-4">--}}
                {{--                            <label class="form-label" for="basic-icon-default-types">Типы анализов</label>--}}
                {{--                            <select id="basic-icon-default-types" name="types[]"--}}
                {{--                                    class="form-control select2 @error('types') is-invalid @enderror"--}}
                {{--                                    multiple>--}}
                {{--                                <option value="" disabled>Выберите тип</option>--}}
                {{--                                @foreach($types as $type)--}}
                {{--                                    <option--}}
                {{--                                        value="{{ $type->id }}" @selected(in_array($type->id, old('types',[])))>{{ $type->name }}</option>--}}
                {{--                                @endforeach--}}
                {{--                            </select>--}}
                {{--                            @error('types')--}}
                {{--                            <div class="invalid-feedback">{{ $message }}</div>--}}
                {{--                            @enderror--}}
                {{--                        </div>--}}

                {{--                        <div class="col-sm-4">--}}
                {{--                            <label class="form-label" for="basic-icon-default-medical_staff">Ответственный медицинский--}}
                {{--                                персонал </label>--}}
                {{--                            <select id="basic-icon-default-medical_staff" name="medical_staff"--}}
                {{--                                    class="form-control select2 @error('medical_staff') is-invalid @enderror">--}}
                {{--                                <option value="" disabled>Выберите персонал</option>--}}
                {{--                                <option value="doctor" @selected('doctor' == old('medical_staff'))>Врач</option>--}}
                {{--                                <option value="assist" @selected('assist' == old('medical_staff'))>Лаборант</option>--}}
                {{--                            </select>--}}

                {{--                            @error('medical_staff')--}}
                {{--                            <div class="invalid-feedback">{{ $message }}</div>--}}
                {{--                            @enderror--}}
                {{--                        </div>--}}

                {{--                        <div class="col-sm-4">--}}
                {{--                            <label class="form-label" for="flatpickr-datetime">Дата и время сдачи анализа</label>--}}
                {{--                            <div class="input-group input-group-merge">--}}
                {{--                                <span id="basic-icon-birth-date2"--}}
                {{--                                      class="input-group-text @error('analysis_time') text-danger icon-border @enderror">--}}
                {{--                                    <i class="bx bx-calendar"></i>--}}
                {{--                                </span>--}}
                {{--                                <input type="text" name="analysis_time" value="{{ old('analysis_time') }}"--}}
                {{--                                       id="flatpickr-datetime"--}}
                {{--                                       class="form-control flatpickr-date-time @error('analysis_time') is-invalid @enderror"--}}
                {{--                                       placeholder="YYYY-MM-DD HH:MM" aria-describedby="basic-icon-birth-date2"/>--}}
                {{--                            </div>--}}
                {{--                            @error('analysis_time')--}}
                {{--                            <div class="invalid-feedback">{{ $message }}</div>--}}
                {{--                            @enderror--}}
                {{--                        </div>--}}

                {{--                        <div class="col-sm-4">--}}
                {{--                            <label class="form-label" for="basic-icon-default-methods">Метод исследования</label>--}}
                {{--                            <select id="basic-icon-default-methods" name="methods[]"--}}
                {{--                                    class="form-control select2 @error('methods') is-invalid @enderror" multiple>--}}
                {{--                                <option value="" disabled>Выберите метод</option>--}}
                {{--                                @foreach($methods as $method)--}}
                {{--                                    <option--}}
                {{--                                        value="{{ $method->id }}" @selected(in_array($method->id, old('methods',[])))>{{ $method->name }}</option>--}}
                {{--                                @endforeach--}}
                {{--                            </select>--}}
                {{--                            @error('methods')--}}
                {{--                            <div class="invalid-feedback">{{ $message }}</div>--}}
                {{--                            @enderror--}}
                {{--                        </div>--}}

                {{--                        <div class="col-sm-4">--}}
                {{--                            <label class="form-label" for="basic-icon-default-equipments">Оборудование</label>--}}
                {{--                            <select id="basic-icon-default-equipments" name="equipments[]"--}}
                {{--                                    class="form-control select2 @error('equipments') is-invalid @enderror"--}}
                {{--                                    multiple>--}}
                {{--                                <option value="" disabled>Выберите оборудования</option>--}}
                {{--                                @foreach($equipments as $equipment)--}}
                {{--                                    <option--}}
                {{--                                        value="{{ $equipment->id }}" @selected(in_array($equipment->id, old('equipments',[])))>{{ $equipment->name }}</option>--}}
                {{--                                @endforeach--}}
                {{--                            </select>--}}
                {{--                            @error('equipments')--}}
                {{--                            <div class="invalid-feedback">{{ $message }}</div>--}}
                {{--                            @enderror--}}
                {{--                        </div>--}}

                {{--                        <div class="col-12 d-flex justify-content-between">--}}
                {{--                            <button class="btn btn-primary btn-prev">--}}
                {{--                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i>--}}
                {{--                                <span class="d-sm-inline-block d-none">Назад</span>--}}
                {{--                            </button>--}}
                {{--                            <button class="btn btn-primary btn-next"><span class="d-sm-inline-block d-none">Далее</span>--}}
                {{--                                <i--}}
                {{--                                    class="bx bx-chevron-right bx-sm me-sm-n2"></i></button>--}}
                {{--                        </div>--}}
                {{--                    </div>--}}
                {{--                </div>--}}

                {{-- Услуги --}}
                {{--                <div id="service" class="content">--}}
                {{--                    <div class="content-header mb-3">--}}
                {{--                        <h6 class="mb-0">Услуги</h6>--}}
                {{--                    </div>--}}

                {{--                    <div class="row g-3">--}}
                {{--                        --}}{{-- status --}}
                {{--                        <input type="hidden" name="service_status" value="assigned">--}}

                {{--                        <div class="col-12">--}}
                {{--                            <label class="form-label" for="service_id">Услуга</label>--}}
                {{--                            <select id="service_id" name="service_id"--}}
                {{--                                    class="form-control select2 @error('service_id') is-invalid @enderror">--}}
                {{--                                <option value="" disabled selected>Выберите услугу</option>--}}
                {{--                                @foreach($services as $service)--}}
                {{--                                    <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>--}}
                {{--                                        {{ $service->name }}--}}
                {{--                                    </option>--}}
                {{--                                @endforeach--}}
                {{--                            </select>--}}
                {{--                            @error('service_id')--}}
                {{--                            <div class="invalid-feedback">{{ $message }}</div>@enderror--}}
                {{--                        </div>--}}

                {{--                        <!-- Service Date -->--}}
                {{--                        <div class="col-sm-4">--}}
                {{--                            <label class="form-label" for="service_date">Дата услуги</label>--}}
                {{--                            <input type="text" name="service_date" id="service_date"--}}
                {{--                                   class="form-control flatpickr-date @error('service_date') is-invalid @enderror"--}}
                {{--                                   value="{{ old('service_date') }}">--}}
                {{--                            @error('service_date')--}}
                {{--                            <div class="invalid-feedback">{{ $message }}</div>@enderror--}}
                {{--                        </div>--}}

                {{--                        <!-- Place -->--}}
                {{--                        <div class="col-sm-4">--}}
                {{--                            <label class="form-label" for="place">Место оказания </label>--}}
                {{--                            <select id="place" name="place"--}}
                {{--                                    class="form-control select2 @error('place') is-invalid @enderror">--}}
                {{--                                @foreach(\App\Models\PatientService::PLACE as $value)--}}
                {{--                                    <option value="{{ $value }}" @selected(old('place') == $value)>--}}
                {{--                                        {{ $value }}--}}
                {{--                                    </option>--}}
                {{--                                @endforeach--}}
                {{--                            </select>--}}
                {{--                            @error('place')--}}
                {{--                            <div class="invalid-feedback">{{ $message }}</div>@enderror--}}
                {{--                        </div>--}}

                {{--                        <!-- Staffs -->--}}
                {{--                        <div class="col-sm-4">--}}
                {{--                            <label class="form-label" for="staffs">Персонал </label>--}}
                {{--                            <select id="staffs" name="staffs[]"--}}
                {{--                                    class="form-control select2 @error('staffs') is-invalid @enderror" multiple>--}}
                {{--                                @foreach($staffs as $staff)--}}
                {{--                                    <option value="{{ $staff->id }}" @selected(in_array($staff->id, old('staffs', [])))>--}}
                {{--                                        {{ $staff->name }}--}}
                {{--                                    </option>--}}
                {{--                                @endforeach--}}
                {{--                            </select>--}}
                {{--                            @error('staffs')--}}
                {{--                            <div class="invalid-feedback">{{ $message }}</div>@enderror--}}
                {{--                        </div>--}}

                {{--                        <div class="mb-3">--}}
                {{--                            <label class="form-label">Процедуры</label>--}}
                {{--                            <div id="procedures-container">--}}
                {{--                            </div>--}}

                {{--                            <button type="button" class="btn btn-primary mt-2" id="btn-add-procedure">--}}
                {{--                                <i class="fas fa-plus me-2"></i> Добавить процедуру--}}
                {{--                            </button>--}}

                {{--                            <template id="procedure-template">--}}
                {{--                                <div class="row mb-2 procedure-row">--}}
                {{--                                    <div class="col-sm-5">--}}
                {{--                                        <select name="procedures[]" class="form-control procedure-select"--}}
                {{--                                                data-placeholder="Выберите или введите новую процедуру" required>--}}
                {{--                                        </select>--}}
                {{--                                    </div>--}}
                {{--                                    <div class="col-sm-5">--}}
                {{--                                        <input type="date" name="procedure_dates[]"--}}
                {{--                                               class="form-control"--}}
                {{--                                               value="{{ now()->format('Y-m-d') }}"--}}
                {{--                                               required>--}}
                {{--                                    </div>--}}
                {{--                                    <div class="col-sm-2">--}}
                {{--                                        <button type="button" class="btn btn-danger btn-remove-procedure">--}}
                {{--                                            <i class="fas fa-trash"></i>--}}
                {{--                                        </button>--}}
                {{--                                    </div>--}}
                {{--                                </div>--}}
                {{--                            </template>--}}
                {{--                        </div>--}}

                {{--                        <div class="mb-3">--}}
                {{--                            <label class="form-label">медикаменты</label>--}}

                {{--                            <div id="medications-container">--}}
                {{--                            </div>--}}

                {{--                            <div class="text-end">--}}
                {{--                                <button type="button" class="btn btn-primary" id="btn-add-medication">--}}
                {{--                                    <i class="fas fa-plus me-2"></i> Добавить медикамент--}}
                {{--                                </button>--}}
                {{--                            </div>--}}

                {{--                            <template id="medication-template">--}}
                {{--                                <div class="card mb-4 medication-row shadow-sm">--}}
                {{--                                    <div class="card-body">--}}
                {{--                                        <div class="d-flex justify-content-between align-items-center mb-3">--}}
                {{--                                            <h6 class="card-title mb-0 medication-title">медикамент #1</h6>--}}
                {{--                                            <button type="button" class="btn btn-sm btn-danger btn-remove-medication">--}}
                {{--                                                <i class="fas fa-trash"></i>--}}
                {{--                                            </button>--}}
                {{--                                        </div>--}}

                {{--                                        <!-- Основной медикамент -->--}}
                {{--                                        <div class="mb-3">--}}
                {{--                                            <label class="form-label">Основной медикамент</label>--}}
                {{--                                            <select name="medications[]" class="form-control medication-select select2"--}}
                {{--                                                    required>--}}
                {{--                                                @foreach($medications as $medication)--}}
                {{--                                                    <option value="{{ $medication->id }}"--}}
                {{--                                                            data-active-substance="{{ $medication->composition->active_substance_name }}">--}}
                {{--                                                        {{ $medication->name }}--}}
                {{--                                                        ({{ $medication->composition->active_substance_name }})--}}
                {{--                                                    </option>--}}
                {{--                                                @endforeach--}}
                {{--                                            </select>--}}
                {{--                                            <div class="d-flex flex-row gap-2 medication_information mt-2"></div>--}}
                {{--                                        </div>--}}

                {{--                                        <!-- Дозировка -->--}}
                {{--                                        <div class="mb-3">--}}
                {{--                                            <label class="form-label">Дозировка</label>--}}
                {{--                                            <input type="text" name="dosages[]" class="form-control"--}}
                {{--                                                   value=""--}}
                {{--                                                   placeholder="Дозировка" required>--}}
                {{--                                        </div>--}}

                {{--                                        <!-- Период приёма -->--}}
                {{--                                        <div class="col-12 mb-3">--}}
                {{--                                            <label class="form-label">Период приёма</label>--}}
                {{--                                            <div class="admission-period-wrapper border rounded p-2">--}}
                {{--                                                <div class="d-flex align-items-center gap-2 mb-2">--}}
                {{--                                                    <select class="form-select w-auto admission-hour">--}}
                {{--                                                        @for($h = 0; $h < 24; $h++)--}}
                {{--                                                            <option--}}
                {{--                                                                value="{{ sprintf('%02d', $h) }}">{{ sprintf('%02d', $h) }}</option>--}}
                {{--                                                        @endfor--}}
                {{--                                                    </select>--}}
                {{--                                                    <span>:</span>--}}
                {{--                                                    <select class="form-select w-auto admission-minute">--}}
                {{--                                                        @for($m = 0; $m < 60; $m += 5)--}}
                {{--                                                            <option--}}
                {{--                                                                value="{{ sprintf('%02d', $m) }}">{{ sprintf('%02d', $m) }}</option>--}}
                {{--                                                        @endfor--}}
                {{--                                                    </select>--}}
                {{--                                                    <button type="button" class="btn btn-outline-success btn-add-time"--}}
                {{--                                                            title="Добавить время">--}}
                {{--                                                        <i class="fas fa-clock"></i>--}}
                {{--                                                    </button>--}}
                {{--                                                </div>--}}
                {{--                                                <div class="admission-times-list d-flex flex-wrap gap-2"></div>--}}
                {{--                                                <input type="hidden" name="admission_periods[]"--}}
                {{--                                                       class="admission-hidden-input">--}}
                {{--                                            </div>--}}
                {{--                                        </div>--}}

                {{--                                        <!-- Совместимые медикаменты -->--}}
                {{--                                        <div class="mb-2 compatible-medications-wrapper">--}}
                {{--                                            <label class="form-label">Совместимые медикаменты</label>--}}
                {{--                                            <div class="compatible-list"></div>--}}

                {{--                                            <div class="row g-2 mt-2">--}}
                {{--                                                <div class="col-md-6">--}}
                {{--                                                    <select class="form-control compatible-medication-select select2">--}}
                {{--                                                        <option value="" disabled selected>Выберите медикамент</option>--}}
                {{--                                                    </select>--}}
                {{--                                                </div>--}}
                {{--                                                <div class="col-md-4">--}}
                {{--                                                    <input type="text" class="form-control compatible-dosage" placeholder="Дозировка">--}}
                {{--                                                </div>--}}
                {{--                                                <div class="col-md-2">--}}
                {{--                                                    <button type="button" class="btn btn-outline-primary w-100 btn-add-compatible">Добавить</button>--}}
                {{--                                                </div>--}}
                {{--                                            </div>--}}
                {{--                                        </div>--}}
                {{--                                    </div>--}}
                {{--                                </div>--}}
                {{--                            </template>--}}
                {{--                        </div>--}}


                {{--                        <div class="col-12 d-flex justify-content-between">--}}
                {{--                            <button class="btn btn-primary btn-prev"><i--}}
                {{--                                    class="bx bx-chevron-left bx-sm ms-sm-n2"></i>--}}
                {{--                                <span class="d-sm-inline-block d-none">Назад</span>--}}
                {{--                            </button>--}}
                {{--                            <button type="submit" class="btn btn-success">Сохранить</button>--}}
                {{--                        </div>--}}
                {{--                    </div>--}}
                {{--                </div>--}}
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(document).ready(function () {
            const phoneInput = $('.phone');

            phoneInput.on('focus', function () {
                if (!$(this).val().startsWith('+ 998')) {
                    $(this).val('+ 998 ');
                }
            });

            phoneInput.mask('+ 998 (00) 000 00 00', {
                placeholder: '+ 998 (__) ___ __ __',
                clearIfNotMatch: true
            });

            phoneInput.on('blur', function () {
                if ($(this).val() === '+ 998 ') {
                    $(this).val('');
                }
            });

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

            function initCompatibleSelect(element) {
                $(element).select2({
                    placeholder: 'Выберите совместимые медикаменты',
                    data: {!! json_encode($medications->map(fn($m) => ['id' => $m->id, 'text' => $m->name])) !!},
                });
            }


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

            // Добавление совместимого медикамента
            $(document).on('click', '.btn-add-compatible', function () {
                const $wrapper = $(this).closest('.compatible-medications-wrapper');
                const $medicationRow = $(this).closest('.medication-row');
                const index = $('.medication-row').index($medicationRow);

                const $select = $wrapper.find('.compatible-medication-select');
                const selectedId = $select.val();
                const selectedText = $select.find('option:selected').text();
                const dosage = $wrapper.find('.compatible-dosage').val();

                if (!selectedId || !dosage) {
                    alert('Пожалуйста, выберите медикамент и введите дозу');
                    return;
                }

                const item = `
                    <div class="input-group compatible-item mb-1">
                        <input type="hidden" name="compatible_medications[${index}][]" value="${selectedId}">
                        <input type="hidden" name="compatible_dosages[${index}][]" value="${dosage}">
                        <span class="input-group-text">${selectedText} (${dosage})</span>
                        <button type="button" class="btn btn-outline-danger btn-remove-compatible">×</button>
                    </div>
                `;
                $wrapper.find('.compatible-list').append(item);
                $select.val(null).trigger('change');
                $wrapper.find('.compatible-dosage').val('');
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

            // Удаление совместимого медикамента
            $(containerMedicaments).on('click', '.btn-remove-compatible', function () {
                $(this).closest('.compatible-item').remove();
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
                    tags: false,
                    placeholder: 'Выберите медикамент',
                });

                bindMedicationChange(element);
            }

            function bindMedicationChange(select) {
                $(select).on('select2:select', function (e) {
                    const selectedId = e.params.data.id;
                    const activeSubstance = $(e.params.data.element).data('active-substance');

                    const $row = $(this).closest('.medication-row');
                    const $dosageInput = $row.find('input[name="dosages[]"]');
                    const $medicationInformation = $row.find('.medication_information');
                    const $compatibleSelect = $row.find('.compatible-medication-select');

                    if (isNaN(selectedId)) {
                        $dosageInput.val('');
                        $compatibleSelect.empty().append('<option selected disabled>Выберите медикамент</option>').trigger('change');
                        return;
                    }

                    // Заполняем дозировку
                    fetch(`/api/medication/show/${selectedId}`)
                        .then(response => response.json())
                        .then(data => {
                            $dosageInput.val(data.dosage ?? '');

                            console.log(data);
                            console.log(data.group);

                            // med. info.
                            let medicationInformationHtml = `<p>Группа: <small> ${data.group.name ?? ''}</small></p>
                            <p>Активное вешество: <small> ${data.composition.active_substance_name ?? ''}</small></p>`;

                            $medicationInformation.html(medicationInformationHtml);
                        })
                        .catch(() => {
                            $dosageInput.val('');
                        });

                    // Загружаем совместимые медикаменты и вставляем в select
                    fetch(`/api/medication/active-substance/${activeSubstance}`)
                        .then(response => response.json())
                        .then(data => {
                            // Удаляем предыдущую select2 (если она есть)
                            if ($compatibleSelect.hasClass('select2-hidden-accessible')) {
                                $compatibleSelect.select2('destroy');
                            }

                            // Чистим и добавляем новые option
                            $compatibleSelect.empty();
                            $compatibleSelect.append('<option selected disabled>Выберите медикамент</option>');

                            data.forEach(item => {
                                $compatibleSelect.append(`
                                <option value="${item.id}" data-dosage="${item.dosage}">
                                    ${item.name} (${item.composition.active_substance_name})
                                </option>
                            `);
                            });

                            // Инициализируем select2 заново
                            $compatibleSelect.select2({
                                placeholder: 'Выберите медикамент',
                                width: '100%',
                            });
                        })
                        .catch(() => {
                            $compatibleSelect.empty().append('<option selected disabled>Ошибка загрузки</option>');
                        });
                });
            }

            // Добавление новой строки
            let medicationCount = 0;
            $('#btn-add-medication').click(function () {
                const clone = templateMedicaments.content.cloneNode(true);
                containerMedicaments.appendChild(clone);

                // Увеличиваем счетчик
                medicationCount++;

                // Обновляем номер в заголовке
                $(containerMedicaments).find('.medication-row').last().find('.medication-title').text(`медикамент # ${medicationCount}`);

                // Инициализируем select2 на новом select с классом .medication-select
                const $lastMedicationSelect = $('.medication-select').last();
                initMedicationSelect($lastMedicationSelect[0]);
            });

            // Удаление строки
            $(containerMedicaments).on('click', '.btn-remove-medication', function () {
                $(this).closest('.medication-row').remove();

                // Пересчитываем количество медикаментов после удаления
                medicationCount = 0;
                $('.medication-row').each(function (index) {
                    $(this).find('.medication-title').text(`медикамент # ${index + 1}`);
                    medicationCount = index + 1;
                });
            });

        })
    </script>
@endsection
