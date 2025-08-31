@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Редактировать назначение</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
    <span class="text-muted fw-light">
        <a class="text-muted" href="{{ route('patients.index') }}">Пациенты</a> /
    </span>
        Редактировать назначение
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

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Редактировать назначение</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('patient.medication.update', $medication->id) }}" method="POST">
                @csrf
                @method('PUT')

                <input type="hidden" name="patient_id" value="{{ $medication->patient_id }}">

                <!-- Основной медикамент -->
                <div class="mb-3">
                    <label class="form-label">Основной медикамент</label>
                    <select name="medications[]" class="form-control medication-select" required>
                        @foreach($allMedications as $med)
                            <option value="{{ $med->id }}"
                                    data-active-substance="{{ $med->composition->active_substance_name }}"
                                {{ $medication->medication_id == $med->id ? 'selected' : '' }}>
                                {{ $med->name }} ({{ $med->composition->active_substance_name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Дозировка -->
                <div class="mb-3">
                    <label class="form-label">Дозировка</label>
                    <input type="text" name="dosages[]" class="form-control"
                           value="{{ old('dosages.0', $medication->dosage) }}" placeholder="Дозировка" required>
                </div>

                <!-- Разовая доза -->
                <div class="mb-3">
                    <label class="form-label">Разовая доза</label>
                    <input type="text" name="single_doses[]" class="form-control"
                           value="{{ old('single_doses.0', $medication->single_dose) }}" placeholder="Разовая доза"
                           required maxlength="50">
                </div>

                <!-- Дата окончания -->
                <div class="mb-3">
                    <label class="form-label">Дата</label>
                    <input type="date" name="date_of_ends[]" class="form-control"
                           value="{{ old('date_of_ends.0', \Carbon\Carbon::parse($medication->date_of_end)->format('Y-m-d')) }}"
                           required>
                </div>

                <!-- Статус -->
                <div class="mb-3">
                    <label class="form-label">Статус</label>
                    <select name="statuses[]" class="form-select" required>
                        @foreach(App\Models\PatientMedication::STATUS as $status)
                            @if($status !== 'cancelled')
                                <option value="{{ $status }}" {{ $medication->status == $status ? 'selected' : '' }}>
                                    {{ App\Models\PatientMedication::STATUS_LABELS[$status] }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Способ приёма -->
                <div class="mb-3">
                    <label class="form-label">Способ приёма</label>
                    <select name="methods[]" class="form-select" required>
                        @foreach(App\Models\PatientMedication::METHOD as $method)
                            <option value="{{ $method }}" {{ $medication->method == $method ? 'selected' : '' }}>
                                {{ App\Models\PatientMedication::METHOD_LABELS[$method] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Правило приёма -->
                <div class="mb-3">
                    <label class="form-label">Правило приёма</label>
                    <select name="admission_rules[]" class="form-select" required>
                        @foreach(App\Models\PatientMedication::ADMISSION_RULES as $rule)
                            <option value="{{ $rule }}" {{ $medication->admission_rules == $rule ? 'selected' : '' }}>
                                {{ App\Models\PatientMedication::ADMISSION_RULE_LABELS[$rule] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Примечания -->
                <div class="mb-3">
                    <label class="form-label">Примечания</label>
                    <textarea name="medication_notes[]" class="form-control" placeholder="Примечания" required
                              maxlength="500">{{ old('medication_notes.0', $medication->medication_notes) }}</textarea>
                </div>

                <!-- Период приёма -->
                <div class="col-12 mb-3">
                    <label class="form-label">Период приёма</label>
                    <div class="admission-period-wrapper border rounded p-2">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <select class="form-select w-auto admission-hour">
                                @for($h = 0; $h < 24; $h++)
                                    <option value="{{ sprintf('%02d', $h) }}">{{ sprintf('%02d', $h) }}</option>
                                @endfor
                            </select>
                            <span>:</span>
                            <select class="form-select w-auto admission-minute">
                                @for($m = 0; $m < 60; $m += 5)
                                    <option value="{{ sprintf('%02d', $m) }}">{{ sprintf('%02d', $m) }}</option>
                                @endfor
                            </select>
                            <button type="button" class="btn btn-outline-success btn-add-time" title="Добавить время">
                                <i class="fas fa-clock"></i>
                            </button>
                        </div>
                        <div class="admission-times-list d-flex flex-wrap gap-2">
                            @foreach(explode(',', $medication->admission_period ?? '') as $time)
                                @if($time)
                                    <span class="badge bg-secondary d-flex align-items-center gap-1"
                                          data-time="{{ $time }}">
                                        {{ $time }}
                                        <button type="button" class="btn-close btn-close-white btn-sm ms-1"
                                                aria-label="Удалить"></button>
                                    </span>
                                @endif
                            @endforeach
                        </div>
                        @php
                            $admissionPeriods = is_array($medication->admission_period)
                                                ? $medication->admission_period
                                                : explode(',', $medication->admission_period ?? '');
                        @endphp

                        <input type="hidden" name="admission_periods[]" class="admission-hidden-input"
                               value="{{ implode(',', $admissionPeriods) }}" required>
                    </div>
                </div>

                <!-- Совместимые медикаменты -->
                <div class="mb-2 compatible-medications-wrapper">
                    <label class="form-label">Совместимые медикаменты</label>
                    <div class="compatible-list">
                        @foreach($medication->compatibleMedications as $comp)
                            <div class="input-group compatible-item mb-1">
                                <input type="hidden" name="compatible_medications[0][]" value="{{ $comp->id }}">
                                <input type="hidden" name="compatible_dosages[0][]" value="{{ $comp->pivot->dosage }}">
                                <span class="input-group-text">{{ $comp->name }} ({{ $comp->pivot->dosage }})</span>
                                <button type="button" class="btn btn-outline-danger btn-remove-compatible">×</button>
                            </div>
                        @endforeach
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-md-6">
                            <select class="form-control compatible-medication-select">
                                <option value="" disabled selected>Выберите медикамент</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control compatible-dosage" placeholder="Дозировка">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-primary w-100 btn-add-compatible">
                                Добавить
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-warning">Обновить назначение</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {

            // Функция обновления скрытого input
            function updateHiddenAdmissionInput($wrapper) {
                const times = [];
                $wrapper.find('.admission-times-list [data-time]').each(function () {
                    times.push($(this).data('time'));
                });
                $wrapper.find('.admission-hidden-input').val(times.join(','));
            }

            // Инициализация уже существующих периодов приёма
            $('.admission-period-wrapper').each(function () {
                updateHiddenAdmissionInput($(this));
            });

            // Добавление времени
            $(document).on('click', '.btn-add-time', function () {
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
            $(document).on('click', '.admission-times-list .btn-close', function () {
                const $wrapper = $(this).closest('.admission-period-wrapper');
                $(this).closest('[data-time]').remove();
                updateHiddenAdmissionInput($wrapper);
            });

            // Добавление совместимого медикамента
            $(document).on('click', '.btn-add-compatible', function () {
                const $wrapper = $(this).closest('.compatible-medications-wrapper');
                const $select = $wrapper.find('.compatible-medication-select');
                const selectedId = $select.val();
                const selectedText = $select.find('option:selected').text();
                const dosage = $wrapper.find('.compatible-dosage').val();

                if (!selectedId || !dosage) {
                    alert('Пожалуйста, выберите медикамент и введите дозу');
                    return;
                }

                const item = $(`
                <div class="input-group compatible-item mb-1">
                    <input type="hidden" name="compatible_medications[0][]" value="${selectedId}">
                    <input type="hidden" name="compatible_dosages[0][]" value="${dosage}">
                    <span class="input-group-text">${selectedText} (${dosage})</span>
                    <button type="button" class="btn btn-outline-danger btn-remove-compatible">×</button>
                </div>
            `);
                $wrapper.find('.compatible-list').append(item);

                $select.val(''); // Сбрасываем значение
                $wrapper.find('.compatible-dosage').val('');
            });

            // Удаление совместимого медикамента
            $(document).on('click', '.btn-remove-compatible', function () {
                $(this).closest('.compatible-item').remove();
            });

            // Подгрузка совместимых медикаментов по основному
            function bindMedicationChange(select) {
                $(select).on('change', function () {
                    const selectedOption = this.options[this.selectedIndex];
                    const activeSubstance = $(selectedOption).data('active-substance');
                    const $wrapper = $(this).closest('.compatible-medications-wrapper');
                    const $compatibleSelect = $wrapper.find('.compatible-medication-select');

                    fetch(`/api/medication/active-substance/${activeSubstance}`)
                        .then(response => response.json())
                        .then(data => {
                            const currentValue = $compatibleSelect.val();
                            $compatibleSelect.empty();
                            $compatibleSelect.append('<option value="" disabled selected>Выберите медикамент</option>');

                            data.forEach(item => {
                                const option = new Option(
                                    `${item.name} (${item.composition.active_substance_name})`,
                                    item.id
                                );
                                $compatibleSelect.append(option);
                            });

                            if (currentValue) $compatibleSelect.val(currentValue);
                        })
                        .catch(err => console.error(err));
                });
            }

            $('.medication-select').each(function () {
                bindMedicationChange(this);
            });

        });
    </script>
@endsection
