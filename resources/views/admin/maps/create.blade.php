@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Создать карту обследования</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('maps.index') }}">Карты обследования </a> /</span>Создать
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
                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-patient">Пациент <span
                                    class="text-danger">*</span></label>
                            <select id="basic-icon-default-patient" name="patient_id"
                                    class="form-control select2 @error('patient_id') is-invalid @enderror" required>
                                <option value="" disabled>Выберите пациента</option>
                                @foreach($patients as $patient)
                                    <option
                                        value="{{ $patient->id }}" @selected($patient->id == old('patient_id'))>{{ $patient->first_name }} - {{  ($patient->id) }}</option>
                                @endforeach
                            </select>
                            @error('patient_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-doctors">Лечащие врачи <span
                                    class="text-danger">*</span></label>
                            <select id="basic-icon-default-doctors" name="doctor_id"
                                    class="form-control select2 @error('doctor_id') is-invalid @enderror" required>
                                <option value="" disabled>Выберите врачей</option>
                                @foreach($doctors as $doctor)
                                    <option
                                        value="{{ $doctor->id }}" @selected($doctor->id == old('doctor_id'))>{{ $doctor->first_name }}</option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-departments">Отделение, к которому
                                прикреплен <span class="text-danger">*</span></label>
                            <select id="basic-icon-default-departments" name="institution_id"
                                    class="form-control select2 @error('institution_id') is-invalid @enderror"
                                    required>
                                <option value="" disabled>Выберите отделение</option>
                                @foreach($institutions as $institution)
                                    <option
                                        value="{{ $institution->id }}" @selected($institution->id == old('institution_id'))>{{ $institution->name }}</option>
                                @endforeach
                            </select>
                            @error('institution_id')
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
                                МКБ-10 <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-first-name2"
                                      class="input-group-text @error('admission_diagnosis_code') text-danger icon-border @enderror">
                                    <i class="bx bx-code"></i>
                                </span>
                                <input type="text" id="basic-icon-default-admission_diagnosis_code"
                                       class="form-control @error('admission_diagnosis_code') is-invalid @enderror"
                                       placeholder="код по МКБ-10 " aria-label="код по МКБ-10 "
                                       name="admission_diagnosis_code"
                                       required
                                       value="{{ old('admission_diagnosis_code') }}"
                                       aria-describedby="basic-icon-default-first-name2"/>
                            </div>
                            @error('admission_diagnosis_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <div>
                            <label class="form-label" for="basic-icon-default-admission_diagnosis_description">Диагноз
                                при госпитализации <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-history2"
                                      class="input-group-text @error('admission_diagnosis_description') text-danger icon-border @enderror">
                                    <i class="bx bx-notepad"></i>
                                </span>
                                <textarea id="basic-icon-default-admission_diagnosis_description"
                                          class="form-control @error('admission_diagnosis_description') is-invalid @enderror"
                                          placeholder="Диагноз при госпитализации"
                                          aria-label="Диагноз при госпитализации"
                                          required
                                          name="admission_diagnosis_description"
                                          aria-describedby="basic-icon-default-history2">{{ old('admission_diagnosis_description') }}</textarea>
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
                                <input type="text" name="discharge_date" value="{{ old('discharge_date') }}"
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
                                       value="{{ old('discharge_diagnosis_code') }}"
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
                                          aria-describedby="basic-icon-default-history2">{{ old('discharge_diagnosis_description') }}</textarea>
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
                            <label class="form-label" for="basic-icon-default-complaints">Жалобы пациента <span
                                    class="text-danger">*</span></label>
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
                                          aria-describedby="basic-icon-default-history2"
                                          required>{{ old('complaints') }}</textarea>
                            </div>
                            @error('complaints')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label" for="basic-icon-default-disease_history">История настоящего
                                заболевания <span class="text-danger">*</span></label>
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
                                          required
                                          aria-describedby="basic-icon-default-history2">{{ old('disease_history') }}</textarea>
                            </div>
                            @error('disease_history')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label" for="basic-icon-default-general_anamnesises">Общий анамнез <span
                                    class="text-danger">*</span></label>
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
                                          required
                                          aria-describedby="basic-icon-default-history2">{{ old('general_anamnesises') }}</textarea>
                            </div>
                            @error('general_anamnesises')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
        })
    </script>
@endsection
