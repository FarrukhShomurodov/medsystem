@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Редактировать карту обследования</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">/</span>Редактировать
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
                    <span class="bs-stepper-label">Госпитализация / Выписка</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#anamnesises">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">3</span>
                    <span class="bs-stepper-label">Анамнез</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#analyzes">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">3</span>
                    <span class="bs-stepper-label">Анализ</span>
                </button>
            </div>

            <div class="line"></div>
            <div class="step" data-target="#survey">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">4</span>
                    <span class="bs-stepper-label">Опросники</span>
                </button>
            </div>

            <div class="line"></div>
            <div class="step" data-target="#service">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">5</span>
                    <span class="bs-stepper-label">Услуги</span>
                </button>
            </div>
        </div>

        <div class="bs-stepper-content">
            <form method="post" action="{{ route('maps.update', $map->id) }}"
                  enctype="multipart/form-data">
                @csrf
                @method('put')

                <!-- Основные данные -->
                <div id="basic-data" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Основные данные</h6>
                    </div>
                    <div class="row g-3">

                        {{--                        <div class="col-sm-4">--}}
                        {{--                            <label class="form-label" for="basic-icon-default-patient">Пациен <span--}}
                        {{--                                    class="text-danger">*</span></label>--}}
                        {{--                            <select id="basic-icon-default-patient" name="patient_id"--}}
                        {{--                                    class="form-control select2 @error('patient_id') is-invalid @enderror" required>--}}
                        {{--                                <option value="" disabled>Выберите пациента</option>--}}
                        {{--                                @foreach($patients as $patient)--}}
                        {{--                                    <option--}}
                        {{--                                        value="{{ $patient->id }}" @selected($patient->id == old('patient_id', $map->patient_id))>{{ $patient->first_name}}--}}
                        {{--                                        - {{  ($patient->id) }} </option>--}}
                        {{--                                @endforeach--}}
                        {{--                            </select>--}}
                        {{--                            @error('patient_id')--}}
                        {{--                            <div class="invalid-feedback">{{ $message }}</div>--}}
                        {{--                            @enderror--}}
                        {{--                        </div>--}}

                        <input type="hidden" name="patient_id" value="{{$map->patient_id}}">

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-doctors">Лечащие врачи <span
                                    class="text-danger">*</span></label>
                            <select id="basic-icon-default-doctors" name="staff_id"
                                    class="form-control select2 @error('staff_id') is-invalid @enderror" required>
                                <option value="" disabled>Выберите врачей</option>
                                @foreach($doctors as $doctor)
                                    <option
                                        value="{{ $doctor->id }}" @selected($doctor->id == old('staff_id',$map->staff_id))>{{ $doctor->name }}</option>
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
                                        value="{{ $institution->id }}" @selected($institution->id == old('department_id', $map->department_id))>{{ $institution->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="is_outpatient"
                                       name="is_outpatient"
                                       value="1"
                                    {{ old('is_outpatient', $map->is_outpatient) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_outpatient">
                                    Амбулаторно
                                </label>
                            </div>
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
                                <input type="text" name="admission_date"
                                       value="{{ old('admission_date', $map->admission_date) }}"
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
                                       value="{{ old('admission_diagnosis_code', $map->admission_diagnosis_code) }}"
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
                                          aria-describedby="basic-icon-default-history2">{{ old('admission_diagnosis_description', $map->admission_diagnosis_description) }}</textarea>
                            </div>
                            @error('admission_diagnosis_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="content-header mb-3 mt-3">
                        <h6 class="mb-0">Выписка</h6>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-discharge_date">Дата выписки</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-birth-date2"
                                      class="input-group-text @error('discharge_date') text-danger icon-border @enderror">
                                    <i class="bx bx-calendar"></i>
                                </span>
                                <input type="text" name="discharge_date"
                                       value="{{ old('discharge_date', $map->discharge_date) }}"
                                       id="basic-icon-discharge_date"
                                       class="form-control flatpickr-date @error('discharge_date') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD" aria-describedby="basic-icon-birth-date2"/>
                            </div>
                            @error('discharge_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-discharge_diagnosis_code">Код по
                                МКБ-10</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-first-name2"
                                      class="input-group-text @error('discharge_diagnosis_code') text-danger icon-border @enderror">
                                    <i class="bx bx-code"></i>
                                </span>
                                <input type="text" id="basic-icon-default-discharge_diagnosis_code"
                                       class="form-control @error('discharge_diagnosis_code') is-invalid @enderror"
                                       placeholder="код по МКБ-10 " aria-label="код по МКБ-10 "
                                       name="discharge_diagnosis_code"
                                       value="{{ old('discharge_diagnosis_code', $map->discharge_diagnosis_code) }}"
                                       aria-describedby="basic-icon-default-first-name2"/>
                            </div>
                            @error('discharge_diagnosis_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label" for="basic-icon-default-discharge_diagnosis_description">Диагноз
                                при выписке </label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-history2"
                                      class="input-group-text @error('discharge_diagnosis_description') text-danger icon-border @enderror">
                                    <i class="bx bx-notepad"></i>
                                </span>
                                <textarea id="basic-icon-default-discharge_diagnosis_description"
                                          class="form-control @error('discharge_diagnosis_description') is-invalid @enderror"
                                          placeholder="Диагноз при выписке"
                                          aria-label="Диагноз при выписке"
                                          name="discharge_diagnosis_description"
                                          aria-describedby="basic-icon-default-history2">{{ old('discharge_diagnosis_description', $map->discharge_diagnosis_description) }}</textarea>
                            </div>
                            @error('discharge_diagnosis_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="content-header mb-3 mt-3">
                        <h6 class="mb-0">Цель</h6>
                    </div>

                    <div class="row g-3">
                        <div>
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
                                          aria-describedby="basic-icon-default-history2">{{ old('examination_purpose', $map->examination_purpose) }}</textarea>
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
                                          aria-describedby="basic-icon-default-history2">{{ old('complaints', $map->complaints) }}</textarea>
                            </div>
                            @error('complaints')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label" for="basic-icon-default-disease_history">История настоящего
                                заболевания</label>
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
                                          aria-describedby="basic-icon-default-history2">{{ old('disease_history', $map->disease_history) }}</textarea>
                            </div>
                            @error('disease_history')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label" for="basic-icon-default-general_anamnesises">Общий анамнез</label>
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
                                          aria-describedby="basic-icon-default-history2">{{ old('general_anamnesises', $map->general_anamnesises) }}</textarea>
                            </div>
                            @error('general_anamnesises')
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


                {{-- Анализ --}}
                <div id="analyzes" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Анализ</h6>
                    </div>

                    @if($map->analyze->isNotEmpty())
                        <div>
                            <div class="content-header mb-3">
                                <h6 class="mb-0">История анализа</h6>
                            </div>
                            @foreach($map->analyze as $analyze)
                                @if($analyze->results->isNotEmpty())
                                    <table class="datatables-users table border-top">
                                        <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Тип анализа</th>
                                            <th>Единица измерения</th>
                                            <th>Значение</th>
                                            <th>Референсный диапазон</th>
                                            <th>Комментарий</th>
                                            <th>Дата</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($analyze->results as $result)
                                            <tr>
                                                <td>{{ $result->id }}</td>
                                                <td>{{ $result->type->name }}</td>
                                                <td>{{ $result->unit }}</td>
                                                <td>{{ $result->value }}</td>
                                                <td>{{ $result->reference_range }}</td>
                                                <td>{{ $result->comment }}</td>
                                                <td>{{ $analyze->created_at->format('d.m.Y') }}</td>
                                            </tr>
                                        @endforeach

                                        </tbody>
                                    </table>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-departments">Лаборатории</label>
                            <select id="basic-icon-default-departments" name="laboratory_id"
                                    class="form-control select2 @error('laboratory_id') is-invalid @enderror">
                                <option value="" selected disabled>Выберите лабораторию</option>
                                @foreach($laboratories as $laboratory)
                                    <option
                                        value="{{ $laboratory->id }}" @selected($laboratory->id == old('laboratory_id'))>{{ $laboratory->name }}</option>
                                @endforeach
                            </select>
                            @error('laboratory_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-types">Типы анализов</label>
                            <select id="basic-icon-default-types" name="types[]"
                                    class="form-control select2 @error('types') is-invalid @enderror" multiple>
                                <option value="" disabled>Выберите тип</option>
                                @foreach($types as $type)
                                    <option
                                        value="{{ $type->id }}" @selected(in_array($type->id, old('types',[])))>{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('types')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-medical_staff">Ответственный медицинский
                                персонал </label>
                            <select id="basic-icon-default-medical_staff" name="medical_staff"
                                    class="form-control select2 @error('medical_staff') is-invalid @enderror">
                                <option value="" selected disabled>Выберите персонал</option>
                                <option value="doctor" @selected('doctor' == old('medical_staff'))>Врач</option>
                                <option value="assist" @selected('assist' == old('medical_staff'))>Лаборант</option>
                            </select>

                            @error('medical_staff')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="flatpickr-datetime">Дата и время сдачи анализа </label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-birth-date2"
                                      class="input-group-text @error('analysis_time') text-danger icon-border @enderror">
                                    <i class="bx bx-calendar"></i>
                                </span>
                                <input type="text" name="analysis_time" value="{{ old('analysis_time') }}"
                                       id="flatpickr-datetime"
                                       required
                                       class="form-control flatpickr-date-time @error('analysis_time') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD HH:MM" aria-describedby="basic-icon-birth-date2"/>
                            </div>
                            @error('analysis_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-methods">Метод исследования</label>
                            <select id="basic-icon-default-methods" name="methods[]"
                                    class="form-control select2 @error('methods') is-invalid @enderror" multiple>
                                <option value="" disabled>Выберите метод</option>
                                @foreach($methods as $method)
                                    <option
                                        value="{{ $method->id }}" @selected(in_array($method->id, old('methods',[])))>{{ $method->name }}</option>
                                @endforeach
                            </select>
                            @error('methods')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-equipments">Оборудование</label>
                            <select id="basic-icon-default-equipments" name="equipments[]"
                                    class="form-control select2 @error('equipments') is-invalid @enderror"
                                    multiple>
                                <option value="" disabled>Выберите оборудования</option>
                                @foreach($equipments as $equipment)
                                    <option
                                        value="{{ $equipment->id }}" @selected(in_array($equipment->id, old('equipments',[])))>{{ $equipment->name }}</option>
                                @endforeach
                            </select>
                            @error('equipments')
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

                {{-- Услуги --}}
                <div id="service" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Услуги</h6>
                    </div>

                    @if($map->services->isNotEmpty())
                        <div>
                            <div class="content-header mb-3">
                                <h6 class="mb-0">История услуг</h6>
                            </div>
                            <table class="datatables-users table border-top">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Услуга</th>
                                    <th>Дата услуги</th>
                                    <th>Время услуги</th>
                                    <th>Место оказания</th>
                                    <th>Результаты</th>
                                    <th>Статус</th>
                                    <th>Статус оплаты</th>
                                    <th>Страховая компания</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($map->services as $service)
                                    <tr>
                                        <td>{{ $service->id }}</td>
                                        <td>{{ $service->service->name }}</td>
                                        <td>{{ $service->service_date }}</td>
                                        <td>{{ $service->service_time }}</td>
                                        <td>{{ $service->place }}</td>
                                        <td>{{ $service->result }}</td>
                                        <td> {!! $service->getStatusBadge() !!} </td>
                                        <td>{{ $service->payment }}</td>
                                        <td>{{ $service->insurance_policy }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="row g-3">
                        {{-- status --}}
                        <input type="hidden" name="service_status" value="assigned">

                        <div class="col-12">
                            <label class="form-label" for="service_id">Услуга</label>
                            <select id="service_id" name="service_id"
                                    class="form-control select2 @error('service_id') is-invalid @enderror">
                                <option value="" disabled selected>Выберите услугу</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>
                                        {{ $service->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_id')
                            <div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Service Date -->
                        <div class="col-sm-4">
                            <label class="form-label" for="service_date">Дата услуги</label>
                            <input type="text" name="service_date" id="service_date"
                                   class="form-control flatpickr-date @error('service_date') is-invalid @enderror"
                                   value="{{ old('service_date') }}">
                            @error('service_date')
                            <div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Place -->
                        <div class="col-sm-4">
                            <label class="form-label" for="place">Место оказания </label>
                            <select id="place" name="place"
                                    class="form-control select2 @error('place') is-invalid @enderror">
                                @foreach(\App\Models\PatientService::PLACE as $value)
                                    <option value="{{ $value }}" @selected(old('place') == $value)>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('place')
                            <div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Staffs -->
                        <div class="col-sm-4">
                            <label class="form-label" for="staffs">Персонал </label>
                            <select id="staffs" name="staffs[]"
                                    class="form-control select2 @error('staffs') is-invalid @enderror" multiple>
                                @foreach($staffs as $staff)
                                    <option value="{{ $staff->id }}" @selected(in_array($staff->id, old('staffs', [])))>
                                        {{ $staff->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('staffs')
                            <div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Процедуры</label>
                            <div id="procedures-container">
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

                        <div class="mb-3">
                            <label class="form-label">Препараты</label>
                            <div id="medications-container">
                                @if($map->services->isNotEmpty())
                                    @foreach($map->services as $service)
                                        @foreach($service->medications as $serviceMedication)
                                            <div class="row mb-2 medication-row">
                                                <div class="col-sm-4">
                                                    <select name="medications[]" class="form-control medication-select select2"
                                                            required>
                                                        @foreach($medications as $medicaton)
                                                            <option value="{{ $medicaton->id }}" @selected($medicaton->id == $serviceMedication->id)>{{ $medicaton->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="text" name="dosages[]"
                                                           class="form-control"
                                                           value="{{$serviceMedication->pivot->dosage}}"
                                                           placeholder="Дозировка"
                                                           required>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="text" name="medication_notes[]"
                                                           class="form-control"
                                                           value="{{$serviceMedication->pivot->notes}}"
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
                                                               value="{{ $serviceMedication->pivot->admission_period }}"
                                                               data-times="{{ $serviceMedication->pivot->admission_period }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-3">
                                                    <button type="button" class="btn btn-danger btn-remove-medication">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endforeach
                                @endif
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

                {{-- Опросники --}}
                <div id="survey" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Опросники</h6>
                    </div>

                    <div class="row g-3">
                        <table class="datatables-users table border-top">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Опросник</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($map->surveyResponses->sortByDesc('id')->take(5) as $response)
                                <tr>
                                    <td>{{$response->id}}</td>
                                    <td>
                                        <p class="text-primary">{{ $response->survey->title }}</p>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info view-response"
                                                data-id="{{ $response->id }}"
                                                data-bs-toggle="modal" data-bs-target="#responseModal">
                                            Просмотр
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <!-- Модальное окно -->
                        <div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel"
                             aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="responseModalLabel">Ответы опросника</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div id="response-content">
                                            <p class="text-muted">Загрузка...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3 mt-3">
                            <div class="col-12">
                                <label class="form-label" for="survey-select">Опросники </label>
                                <select id="survey-select" name="survey_id"
                                        class="form-control select2 @error('survey_id') is-invalid @enderror">
                                    <option value="" disabled selected>Выберите опросник</option>
                                    @foreach($surveys as $survey)
                                        <option
                                            value="{{ $survey->id }}" @selected($survey->id == old('survey_id'))>{{ $survey->title }}</option>
                                    @endforeach
                                </select>
                                @error('survey_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Question container --}}
                            <div id="questions-container" class="mt-3"></div>
                        </div>

                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev"><i
                                    class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                                <span class="d-sm-inline-block d-none">Назад</span>
                            </button>
                            <button type="submit" class="btn btn-warning ">Редактировать</button>
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

            // опросники
            $('#survey-select').on('change', function () {
                let surveyId = $(this).val();
                let questionsContainer = $('#questions-container');

                questionsContainer.html('');

                if (!surveyId) return;

                $.ajax({
                    url: `/api/surveys/${surveyId}/questions`,
                    type: 'GET',
                    success: function (data) {
                        questionsContainer.append('<button type="button" class="mb-3 btn btn-sm btn-warning" id="clear_survey">Очистить</button>');

                        // Обрабатываем группы
                        if (data.groups.length > 0) {
                            data.groups.forEach(group => {
                                let groupHtml = `
                        <div class="card mb-4 border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0 text-white">${group.title}</h5>
                            </div>
                            <div class="card-body">
                    `;
                                group.questions.forEach((q, index) => {
                                    groupHtml += generateQuestionHtml(q, index + 1);
                                });
                                groupHtml += '</div></div>';
                                questionsContainer.append(groupHtml);
                            });
                        }

                        // Обрабатываем отдельные вопросы
                        if (data.standalone_questions.length > 0) {
                            let standaloneHtml = `
                    <div class="card mb-4 border-secondary">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0 text-white">Общие вопросы</h5>
                        </div>
                        <div class="card-body">
                `;
                            data.standalone_questions.forEach((q, index) => {
                                standaloneHtml += generateQuestionHtml(q, index + 1);
                            });
                            standaloneHtml += '</div></div>';
                            questionsContainer.append(standaloneHtml);
                        }

                        function generateQuestionHtml(q, index) {
                            return `
                    <div class="mb-3 question-item">
                        <label class="form-label fw-semibold" data-question="${q.id}" >${index}. ${q.question}</label>
                        ${getInputField(q, index)}
                    </div>
                `;
                        }

                        function getInputField(q) {
                            if (q.type === 'choice') {
                                return q.options.map(option => `
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                   name="answers[${q.id}]"
                                   value="${option.text}"
                                   required>
                            <label class="form-check-label">${option.text}</label>
                        </div>
                    `).join('');
                            }
                            if (q.type === 'boolean') {
                                return `
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                   name="answers[${q.id}]"
                                   value="yes" required> Да
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                   name="answers[${q.id}]"
                                   value="no" required> Нет
                        </div>
                    `;
                            }
                            return `<input type="text" name="answers[${q.id}]"
                             class="form-control" required>`;
                        }

                        $('#service-container').show();
                    },
                    error: function () {
                        questionsContainer.html('<p class="text-danger">Ошибка загрузки вопросов</p>');
                    }
                });
            });

            $(document).on('click', '#clear_survey', function () {
                $('#questions-container').html('');
                $('#survey-select').val('').trigger('change');
            });

            function getInputField(q, index) {
                console.log("OPTIONS RAW:", q.options); // Отладка

                let options = q.options;

                if (q.type === 'choice') {
                    return options.map(option => `
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="answers[${q.id}]" value="${option.text}" required>
                            <label class="form-check-label">${option.text}</label>
                        </div>
                    `).join('');
                }
                if (q.type === 'boolean') {
                    return `
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="answers[${q.id}]" value="yes" required> Да
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="answers[${q.id}]" value="no" required> Нет
                        </div>
                    `;
                }
                return `<input type="text" name="answers[${q.id}]" class="form-control" required>`;
            }


            // Функция загрузки ответов в модальное окно
            $('.view-response').on('click', function () {
                let responseId = $(this).data('id');
                let modalBody = $('#response-content');

                modalBody.html('<p class="text-muted">Загрузка...</p>');

                $.ajax({
                    url: `/api/surveys-response/show/${responseId}`,
                    type: 'GET',
                    success: function (response) {
                        console.log(response);

                        let html = `<h6 class="text-primary">${response.survey.title}</h6>`;

                        if (response.answers.length > 0) {
                            response.answers.forEach((answer, index) => {
                                let localizedAnswer = answer.answer;

                                if (localizedAnswer.toLowerCase() === 'yes') localizedAnswer = 'Да';
                                if (localizedAnswer.toLowerCase() === 'no') localizedAnswer = 'Нет';

                                html += `
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input select-answer"
                                   data-question-id="${answer.question.id}"
                                   data-answer="${answer.answer}">
                            <label class="form-check-label">
                                <strong>${index + 1}. ${answer.question.question}</strong>
                                <br>Ответ: <span class="text-success">${localizedAnswer}</span>
                            </label>
                        </div>
                    `;
                            });

                            html += `<p class="btn btn-sm btn-primary mt-2" id="apply-selected-answers">Использовать выбранные</p>`;
                        } else {
                            html += '<p class="text-muted">Ответов нет</p>';
                        }

                        modalBody.html(html);
                    },
                    error: function () {
                        modalBody.html('<p class="text-danger">Ошибка загрузки данных</p>');
                    }
                });
            });

            // Обработчик для кнопки "Использовать выбранные"
            $(document).on('click', '#apply-selected-answers', function () {
                $('.select-answer:checked').each(function () {
                    let questionId = $(this).data('question-id');
                    let answer = $(this).data('answer');

                    let targetInput = $(`input[name="answers[${questionId}]"]`);
                    let targetRadio = $(`input[name="answers[${questionId}]"][value="${answer}"]`);

                    let question = $(`label[data-question="${questionId}"]`);

                    question.append('<small> (Ответ скопирован) </small>');

                    if (targetInput.length > 0) {
                        if (targetInput.attr('type') === 'radio') {
                            if (targetRadio.length > 0) {
                                targetRadio.prop('checked', true);
                            } else {
                                alert(`Ответ "${answer}" не найден среди вариантов.`);
                            }
                        } else {
                            targetInput.val(answer);
                        }
                    } else {
                        alert(`Вопрос с ID ${questionId} не найден.`);
                    }
                });

                $('#responseModal').modal('hide');
            });
        })
    </script>
@endsection
