@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Редактировать анализ</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('analyzes.index') }}">Анализы </a> /</span>Редактировать
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
            <div class="step" data-target="#info">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">2</span>
                    <span class="bs-stepper-label">Направления</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#additional_data">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">3</span>
                    <span class="bs-stepper-label">Дополнительные данные</span>
                </button>
            </div>
        </div>

        <div class="bs-stepper-content">
            <form method="post" action="{{ route('analyzes.update', $analyze->id) }}"
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
                            <label class="form-label" for="basic-icon-default-patient">Пациент <span
                                    class="text-danger">*</span></label>
                            <select id="basic-icon-default-patient" name="patient_id"
                                    class="form-control select2 @error('patient_id') is-invalid @enderror" required>
                                <option value="" disabled selected>Выберите пациента</option>
                                @foreach($patients as $patient)
                                    <option
                                        value="{{ $patient->id }}" @selected($patient->id == old('patient_id', $analyze->patient_id))>{{ $patient->first_name }}</option>
                                @endforeach
                            </select>
                            @error('patient_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="patient-map">Карты</label>
                            <select id="patient-map" name="examination_map_id"
                                    class="form-control select2 @error('examination_map_id') is-invalid @enderror">
                                <option value="" disabled selected>Выберите карту</option>
                            </select>
                            @error('examination_map_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-departments">Лаборатории <span
                                    class="text-danger">*</span></label>
                            <select id="basic-icon-default-departments" name="laboratory_id"
                                    class="form-control select2 @error('laboratory_id') is-invalid @enderror"
                                    required>
                                <option value="" disabled>Выберите лабораторию</option>
                                @foreach($laboratories as $laboratory)
                                    <option
                                        value="{{ $laboratory->id }}" @selected($laboratory->id == old('laboratory_id', $analyze->laboratory_id))>{{ $laboratory->name }}</option>
                                @endforeach
                            </select>
                            @error('laboratory_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-types">Типы анализов <span
                                    class="text-danger">*</span></label>
                            <select id="basic-icon-default-types" name="types[]"
                                    class="form-control select2 @error('types') is-invalid @enderror"
                                    multiple
                                    required>
                                <option value="" disabled>Выберите тип</option>
                                @foreach($types as $type)
                                    <option
                                        value="{{ $type->id }}" @selected(in_array($type->id, old('types',$analyze->types->pluck('id')->toArray())))>{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('types')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-medical_staff">Ответственный медицинский
                                персонал <span
                                    class="text-danger">*</span></label>
                            <select id="basic-icon-default-medical_staff" name="medical_staff"
                                    class="form-control select2 @error('medical_staff') is-invalid @enderror" required>
                                <option value="" disabled>Выберите персонал</option>
                                <option
                                    value="doctor" @selected('doctor' == old('medical_staff', $analyze->medical_staff))>
                                    Врач
                                </option>
                                <option
                                    value="assist" @selected('assist' == old('medical_staff', $analyze->medical_staff))>
                                    Лаборант
                                </option>
                            </select>
                            @error('medical_staff')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="flatpickr-datetime">Дата и время сдачи анализа <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-birth-date2"
                                      class="input-group-text @error('analysis_time') text-danger icon-border @enderror">
                                    <i class="bx bx-calendar"></i>
                                </span>
                                <input type="text" name="analysis_time"
                                       value="{{ old('analysis_time', $analyze->analysis_time) }}"
                                       id="flatpickr-datetime"
                                       required
                                       class="form-control flatpickr-date-time @error('analysis_time') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD HH:MM" aria-describedby="basic-icon-birth-date2"/>
                            </div>
                            @error('analysis_time')
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

                {{-- Направления --}}
                <div id="info" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Направления</h6>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="basic-icon-default-doctors">Лечащие врачи <span
                                    class="text-danger">*</span></label>
                            <select id="basic-icon-default-doctors" name="doctor_id"
                                    class="form-control select2 @error('doctor_id') is-invalid @enderror" required>
                                @foreach($doctors as $doctor)
                                    <option
                                        value="{{ $doctor->id }}" @selected($doctor->id == old('doctor_id', $analyze->doctor_id))>{{ $doctor->first_name }}</option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="basic-icon-default-target">Цель
                                анализа <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-history2"
                                      class="input-group-text @error('target') text-danger icon-border @enderror">
                                    <i class="bx bx-notepad"></i>
                                </span>
                                <textarea id="basic-icon-default-target"
                                          class="form-control @error('target') is-invalid @enderror"
                                          placeholder="Цель анализа"
                                          aria-label="Цель анализа"
                                          name="target"
                                          required
                                          aria-describedby="basic-icon-default-history2">{{ old('target', $analyze->target) }}</textarea>
                            </div>
                            @error('target')
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

                {{-- Дополнительные данные --}}
                <div id="additional_data" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Дополнительные данные</h6>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-methods">Метод исследования <span
                                    class="text-danger">*</span></label>
                            <select id="basic-icon-default-methods" name="methods[]"
                                    class="form-control select2 @error('methods') is-invalid @enderror" multiple
                                    required>
                                <option value="" disabled>Выберите метод</option>
                                @foreach($methods as $method)
                                    <option
                                        value="{{ $method->id }}" @selected(in_array($method->id, old('methods', $analyze->methods->pluck('id')->toArray())))>{{ $method->name }}</option>
                                @endforeach
                            </select>
                            @error('methods')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-equipments">Оборудование <span
                                    class="text-danger">*</span></label>
                            <select id="basic-icon-default-equipments" name="equipments[]"
                                    class="form-control select2 @error('equipments') is-invalid @enderror"
                                    multiple
                                    required>
                                <option value="" disabled>Выберите оборудования</option>
                                @foreach($equipments as $equipment)
                                    <option
                                        value="{{ $equipment->id }}" @selected(in_array($equipment->id, old('equipments', $analyze->equipments->pluck('id')->toArray())))>{{ $equipment->name }}</option>
                                @endforeach
                            </select>
                            @error('equipments')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="col-sm-4">
                            <label class="form-label" for="flatpickr-expected_result_date">Срок готовности (ожидаемая
                                дата
                                результата) <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-birth-date2"
                                      class="input-group-text @error('expected_result_date') text-danger icon-border @enderror">
                                    <i class="bx bx-calendar"></i>
                                </span>
                                <input type="text" name="expected_result_date"
                                       value="{{ old('expected_result_date', $analyze->expected_result_date) }}"
                                       id="flatpickr-expected_result_date"
                                       required
                                       class="form-control flatpickr-date @error('expected_result_date') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD" aria-describedby="basic-icon-birth-date2"/>
                            </div>
                            @error('expected_result_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="basic-icon-default-status">Статус <span
                                    class="text-danger">*</span></label>
                            <select id="basic-icon-default-status" name="status"
                                    class="form-control select2 @error('status') is-invalid @enderror"
                                    readonly="readonly"
                                    required>
                                <option
                                    value="assigned" selected>
                                    Назначен
                                </option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="basic-icon-default-notes">Примичание <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-history2"
                                      class="input-group-text @error('notes') text-danger icon-border @enderror">
                                    <i class="bx bx-notepad"></i>
                                </span>
                                <textarea id="basic-icon-default-notes"
                                          class="form-control @error('notes') is-invalid @enderror"
                                          placeholder="Примичание"
                                          aria-label="Примичание"
                                          name="notes"
                                          required
                                          aria-describedby="basic-icon-default-history2">{{ old('notes', $analyze->notes) }}</textarea>
                            </div>
                            @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

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
            $('#basic-icon-default-patient').on('change', function () {
                const patientId = $(this).val();
                const mapSelect = $('#patient-map');

                mapSelect.html('<option value="" disabled selected>Загрузка...</option>');

                $.ajax({
                    url: `/api/examination-map-by-patient/${patientId}`,
                    type: 'GET',
                    success: function (data) {
                        console.log(data)
                        mapSelect.html('<option value="" disabled selected>Выберите карту</option>');
                        data.forEach(map => {
                            $('#patient-map').append(
                                `<option value="${map.id}">${map.patient_name} — ${map.admission_date} — ${map.institution_name} — ${map.diagnosis}</option>`
                            );
                        });
                    },
                    error: function () {
                        mapSelect.html('<option value="" disabled selected>Ошибка загрузки карт</option>');
                    }
                });
            });
        });
    </script>
@endsection
