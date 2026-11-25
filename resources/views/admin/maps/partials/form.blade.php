{{--
    The examination map (hospital stay) wizard of maps/create and maps/edit.

    @include('admin.maps.partials.form', ['map' => $map ?? null, 'patientId' => $patient->id])

    map: the stay being edited, or null for a new one. The edit form adds the outpatient flag, the
    discharge and the prescriptions of the stay; it is saved only by staff with update_maps.
    $doctors, $departments and $initialAssessment come from the page.
--}}
@php
    $value = fn (string $name) => old($name, $map?->{$name});
    $steps = ['basic-data' => 'Основные данные', 'contact-info' => $map ? 'Госпитализация и выписка' : 'Госпитализация', 'anamnesises' => 'Анамнез'];

    if ($map) {
        $steps['service'] = 'Медикаменты';
    }

    $canSave = $map ? auth()->user()?->can('update_maps') : true;
    $textarea = fn (string $name, string $label, array $extra = []) => ['field' => [
        'name' => $name, 'label' => $label, 'type' => 'textarea', 'icon' => 'bx-notepad', 'col' => 'col-12', 'value' => $value($name), ...$extra,
    ]];
@endphp

<div class="bs-stepper wizard-vertical vertical">
    <div class="bs-stepper-header" role="tablist">
        @foreach ($steps as $stepId => $stepLabel)
            <div class="step" data-target="#{{ $stepId }}">
                <button type="button" class="step-trigger" role="tab" aria-controls="{{ $stepId }}">
                    <span class="bs-stepper-circle">{{ $loop->iteration }}</span>
                    <span class="bs-stepper-label">{{ $stepLabel }}</span>
                </button>
            </div>
        @endforeach
    </div>

    <div class="bs-stepper-content">
        <form method="POST" action="{{ $map ? route('maps.update', $map->id) : route('maps.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($map)
                @method('PUT')
            @endif
            <input type="hidden" name="patient_id" value="{{ $patientId }}">

            <div id="basic-data" class="content" role="tabpanel">
                <div class="content-header mb-3"><h2 class="h3 mb-0">Основные данные</h2></div>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label required" for="staff_id">Лечащий врач</label>
                        <select id="staff_id" name="staff_id" class="form-select select2 @error('staff_id') is-invalid @enderror" required>
                            <option value="" disabled @selected(blank($value('staff_id')))>Выберите врача</option>
                            @foreach ($doctors as $doctor)
                                <option value="{{ $doctor->id }}" @selected($doctor->id == $value('staff_id'))>{{ $doctor->full_name }}</option>
                            @endforeach
                        </select>
                        @error('staff_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label required" for="department_id">Отделение, к которому прикреплён</label>
                        <select id="department_id" name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                            <option value="" disabled @selected(blank($value('department_id')))>Выберите отделение</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected($department->id == $value('department_id'))>{{ $department->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    @if ($map)
                        <div class="col-sm-6">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_outpatient" name="is_outpatient" value="1"
                                    @checked(old('is_outpatient', $map->is_outpatient))>
                                <span class="form-check-label">Амбулаторно</span>
                            </label>
                        </div>
                    @endif
                </div>
            </div>

            <div id="contact-info" class="content" role="tabpanel">
                <div class="content-header mb-3"><h2 class="h3 mb-0">Госпитализация</h2></div>
                <div class="row g-3">
                    @include('admin.patients.partials.field', ['field' => [
                        'name' => 'admission_date', 'label' => 'Дата госпитализации', 'icon' => 'bx-calendar', 'required' => true, 'col' => 'col-sm-6',
                        'class' => 'flatpickr-date', 'placeholder' => 'ГГГГ-ММ-ДД', 'value' => $value('admission_date'),
                    ]])
                    @include('admin.patients.partials.field', ['field' => [
                        'name' => 'admission_diagnosis_code', 'label' => 'Код по МКБ-10', 'icon' => 'bx-code', 'col' => 'col-sm-6',
                        'value' => $value('admission_diagnosis_code'),
                    ]])
                    @include('admin.patients.partials.field', $textarea('admission_diagnosis_description', 'Диагноз при госпитализации'))
                </div>

                @if ($map)
                    <div class="content-header mb-3 mt-4"><h3 class="subheader mb-0">Выписка</h3></div>
                    <div class="row g-3">
                        @include('admin.patients.partials.field', ['field' => [
                            'name' => 'discharge_date', 'label' => 'Дата выписки', 'icon' => 'bx-calendar', 'col' => 'col-sm-6',
                            'class' => 'flatpickr-date', 'placeholder' => 'ГГГГ-ММ-ДД', 'value' => $value('discharge_date'),
                            'attributes' => ['data-max-date' => 'today', 'aria-describedby' => 'discharge-date-hint'],
                        ]])
                        @include('admin.patients.partials.field', ['field' => [
                            'name' => 'discharge_diagnosis_code', 'label' => 'Код по МКБ-10', 'icon' => 'bx-code', 'col' => 'col-sm-6',
                            'value' => $value('discharge_diagnosis_code'),
                        ]])
                        <div class="col-12">
                            <div class="form-hint" id="discharge-date-hint">Сохранённая дата выписки закрывает госпитализацию: после этого карту нельзя изменить.</div>
                        </div>
                        @include('admin.patients.partials.field', $textarea('discharge_diagnosis_description', 'Диагноз при выписке'))
                    </div>
                @endif

                <div class="content-header mb-3 mt-4"><h3 class="subheader mb-0">Цель</h3></div>
                <div class="row g-3">
                    @include('admin.patients.partials.field', $textarea('examination_purpose', 'Цель обследования'))
                </div>
            </div>

            <div id="anamnesises" class="content" role="tabpanel">
                <div class="content-header mb-3"><h2 class="h3 mb-0">Анамнез</h2></div>
                <div class="row g-3">
                    @include('admin.patients.partials.field', $textarea('complaints', 'Жалобы пациента'))
                    @include('admin.patients.partials.field', $textarea('disease_history', 'История настоящего заболевания'))
                    @include('admin.patients.partials.field', $textarea('general_anamnesises', 'Общий анамнез'))

                    <div class="col-12">
                        <div class="form-label">Первичный сестринский осмотр</div>
                        <div class="btn-list">
                            @if ($initialAssessment)
                                <a href="{{ route('documents.show.answer', $initialAssessment) }}" class="btn btn-success" target="_blank">
                                    <i class="bx bx-show" aria-hidden="true"></i>Смотреть осмотр
                                </a>
                                @unless ($map)
                                    {{-- Submits #delete-first-document-form below: forms cannot be nested. --}}
                                    <button type="submit" form="delete-first-document-form" class="btn btn-outline-danger">
                                        <i class="bx bx-trash" aria-hidden="true"></i>Удалить осмотр
                                    </button>
                                @endunless
                            @else
                                <a href="{{ route('documents.show', ['document' => \App\Models\Document::INITIAL_NURSING_ASSESSMENT, 'patient_id' => $patientId]) }}"
                                   class="btn btn-success" @if ($map) target="_blank" @endif>
                                    <i class="bx bx-file" aria-hidden="true"></i>Заполнить осмотр
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if ($map)
                <div id="service" class="content" role="tabpanel">
                    <div class="content-header mb-3">
                        <h2 class="h3 mb-1">Медикаменты</h2>
                        {{-- Prescriptions are made and changed on the patient card (patient.medication.*); the map form does not save them. --}}
                        <p class="text-secondary mb-0">Назначения этой госпитализации. Назначить или изменить медикамент можно в карточке пациента.</p>
                    </div>

                    @if ($prescriptions->isEmpty())
                        <x-empty-state title="Медикаменты не назначены" icon="bx-capsule" />
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter">
                                <thead>
                                <tr>
                                    <th class="w-1">#</th>
                                    <th>Медикамент</th>
                                    <th>Активное вещество</th>
                                    <th>Группа</th>
                                    <th>Дозировка</th>
                                    <th>Время приёма</th>
                                    <th>Совместимые медикаменты</th>
                                    <th>Статус</th>
                                    @can('edit_patient_medication')
                                        <th class="w-1"><span class="visually-hidden">Действия</span></th>
                                    @endcan
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($prescriptions as $prescription)
                                    <tr>
                                        <td class="text-secondary">{{ $loop->iteration }}</td>
                                        <td>{{ $prescription->medication?->name ?? '—' }}</td>
                                        <td>{{ $prescription->medication?->composition?->active_substance_name ?? '—' }}</td>
                                        <td>{{ $prescription->medication?->group?->name ?? 'Не указана' }}</td>
                                        <td>{{ $prescription->dosage }}</td>
                                        <td>{{ $prescription->admission_period }}</td>
                                        <td>
                                            @foreach ($prescription->compatibleMedications as $compatible)
                                                <div>{{ $compatible->name }} ({{ $compatible->pivot->dosage }})</div>
                                            @endforeach
                                        </td>
                                        <td><x-status-icon :status="$prescription->status" decorative /> {{ $prescription->status->label() }}</td>
                                        @can('edit_patient_medication')
                                            <td>
                                                <a href="{{ route('patient.medication.edit', $prescription) }}" class="btn btn-sm">Изменить</a>
                                            </td>
                                        @endcan
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif

            {{-- One row of buttons under every step; maps.update needs update_maps, edit_maps only opens the form. --}}
            <div class="d-flex justify-content-between gap-2 mt-4 pt-3 border-top">
                <button type="button" class="btn btn-prev"><i class="bx bx-chevron-left" aria-hidden="true"></i>Назад</button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-next">Далее<i class="bx bx-chevron-right ms-1" aria-hidden="true"></i></button>
                    @if ($canSave)
                        <button type="submit" class="btn btn-primary"><i class="bx bx-save" aria-hidden="true"></i>Сохранить</button>
                    @endif
                </div>
            </div>
        </form>

        @if (! $map && $initialAssessment)
            <form id="delete-first-document-form" method="POST" class="d-none"
                  action="{{ route('documents.show.answer.delete', $initialAssessment) }}"
                  data-confirm="Удалить первичный сестринский осмотр? Действие нельзя отменить.">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
</div>
