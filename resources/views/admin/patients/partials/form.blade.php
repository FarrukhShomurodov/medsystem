{{--
    The patient wizard of patients/create and patients/edit: basic data, contacts and address,
    insurance, and on the edit form the visit history.

    @include('admin.patients.partials.form', ['patient' => $patient ?? null])

    patient: the patient being edited, or null for a new one. $regions and $cities come from the page.
--}}
@use('App\Support\Display')
@php
    $value = fn (string $name, mixed $default = null) => old($name, $patient ? $patient->{$name} : $default);
    $steps = ['basic-data' => 'Основные данные', 'contact-info' => 'Контакты и адрес', 'insurance' => 'Страховка'];

    if ($patient) {
        $steps['visit-history'] = 'История обращений';
    }

    $lastStep = array_key_last($steps);
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
        <form method="POST" action="{{ $patient ? route('patients.update', $patient->id) : route('patients.store') }}"
              enctype="multipart/form-data">
            @csrf
            @if ($patient)
                @method('PUT')
            @endif

            <div id="basic-data" class="content" role="tabpanel">
                <div class="content-header mb-3"><h2 class="h3 mb-0">Основные данные</h2></div>
                <div class="row g-3">
                    @include('admin.patients.partials.basic-fields', ['patient' => $patient])

                    @if ($patient && $patient->files->isNotEmpty())
                        <div class="col-12">
                            <div class="form-label">Прикреплённые файлы</div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($patient->files as $file)
                                    <div class="d-flex align-items-center gap-2 border rounded px-3 py-2 file_container">
                                        <span class="text-truncate" title="{{ $file->name }}">{{ \Illuminate\Support\Str::limit($file->name, 40) }}</span>
                                        <a href="{{ route('patients.files.show', [$patient, $file]) }}" target="_blank"
                                           class="btn btn-sm btn-icon btn-ghost-secondary" aria-label="Открыть {{ $file->name }}" title="Открыть">
                                            <i class="bx bx-show" aria-hidden="true"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-danger delete_file"
                                                data-url="{{ route('api.patients.files.destroy', [$patient, $file]) }}"
                                                aria-label="Удалить {{ $file->name }}" title="Удалить">
                                            <i class="bx bx-trash" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div id="contact-info" class="content" role="tabpanel">
                <div class="content-header mb-3"><h2 class="h3 mb-0">Контактная информация</h2></div>
                <div class="row g-3">
                    @include('admin.patients.partials.field', ['field' => [
                        'name' => 'phone', 'id' => 'phone', 'label' => 'Телефон', 'icon' => 'bx-phone', 'required' => true,
                        'placeholder' => '+998 (XX) XXX XX XX', 'value' => $value('phone'),
                        'attributes' => ['inputmode' => 'tel', 'autocomplete' => 'tel'],
                    ]])
                    @include('admin.patients.partials.field', ['field' => [
                        'name' => 'additional_phone', 'label' => 'Дополнительный телефон', 'icon' => 'bx-phone',
                        'placeholder' => '+998 (XX) XXX XX XX', 'value' => $value('additional_phone'),
                        'attributes' => ['inputmode' => 'tel'],
                    ]])
                    @include('admin.patients.partials.field', ['field' => [
                        'name' => 'email', 'label' => 'Email', 'icon' => 'bx-envelope', 'type' => 'email', 'value' => $value('email'),
                    ]])
                </div>

                <div class="content-header mb-3 mt-4"><h3 class="subheader mb-0">Адрес проживания</h3></div>
                <div class="row g-3">
                    @include('admin.patients.partials.address-fields', ['prefix' => 'residential', 'region' => 'region_id', 'city' => 'city_id', 'value' => $value])
                </div>

                <div class="content-header d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 mt-4">
                    <h3 class="subheader mb-0">Адрес регистрации (если отличается от проживания)</h3>
                    <button type="button" id="copy_address" class="btn btn-sm">
                        <i class="bx bx-copy" aria-hidden="true"></i>Как адрес проживания
                    </button>
                </div>
                <div class="row g-3">
                    @include('admin.patients.partials.address-fields', ['prefix' => 'registered', 'region' => 'registered_region_id', 'city' => 'registered_city_id', 'value' => $value])
                </div>

                <div class="content-header mb-3 mt-4"><h3 class="subheader mb-0">Экстренный контакт</h3></div>
                <div class="row g-3">
                    @include('admin.patients.partials.field', ['field' => [
                        'name' => 'emergency_contact_full_name', 'label' => 'ФИО контактного лица', 'icon' => 'bx-user', 'col' => 'col-sm-6',
                        'value' => $value('emergency_contact_full_name'),
                    ]])
                    @include('admin.patients.partials.field', ['field' => [
                        'name' => 'emergency_contact_relation', 'label' => 'Связь с пациентом (родственник, друг…)', 'icon' => 'bx-user-circle',
                        'col' => 'col-sm-6', 'value' => $value('emergency_contact_relation'),
                    ]])
                    @include('admin.patients.partials.field', ['field' => [
                        'name' => 'emergency_contact_phone', 'label' => 'Телефон', 'icon' => 'bx-phone', 'col' => 'col-sm-6',
                        'placeholder' => '+998 (XX) XXX XX XX', 'value' => $value('emergency_contact_phone'),
                        'attributes' => ['inputmode' => 'tel'],
                    ]])
                    @include('admin.patients.partials.field', ['field' => [
                        'name' => 'emergency_contact_address', 'label' => 'Адрес', 'icon' => 'bx-map', 'col' => 'col-sm-6',
                        'value' => $value('emergency_contact_address'),
                    ]])
                </div>
            </div>

            <div id="insurance" class="content" role="tabpanel">
                <div class="content-header mb-3"><h2 class="h3 mb-0">Страховка</h2></div>
                <div class="row g-3">
                    @include('admin.patients.partials.field', ['field' => [
                        'name' => 'insurance_policy_number', 'label' => 'Номер полиса', 'icon' => 'bx-file', 'col' => 'col-sm-6',
                        'value' => $value('insurance_policy_number'),
                    ]])
                    @include('admin.patients.partials.field', ['field' => [
                        'name' => 'insurance_company', 'label' => 'Страховая компания', 'icon' => 'bx-building', 'col' => 'col-sm-6',
                        'value' => $value('insurance_company'),
                    ]])
                    @include('admin.patients.partials.field', ['field' => [
                        'name' => 'insurance_type', 'label' => 'Тип страхования', 'icon' => 'bx-shield', 'col' => 'col-sm-6',
                        'value' => $value('insurance_type'),
                    ]])
                    @include('admin.patients.partials.field', ['field' => [
                        'name' => 'insurance_expiry_date', 'label' => 'Срок действия полиса', 'icon' => 'bx-calendar', 'col' => 'col-sm-6',
                        'class' => 'flatpickr-date', 'placeholder' => 'ГГГГ-ММ-ДД', 'value' => $value('insurance_expiry_date'),
                    ]])
                </div>
            </div>

            @if ($patient)
                <div id="visit-history" class="content" role="tabpanel">
                    <div class="content-header mb-3">
                        <h2 class="h3 mb-1">История обращений</h2>
                        <p class="text-secondary mb-0">Посещений: {{ $maps->count() }}</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                            <tr>
                                <th class="w-1">#</th>
                                <th>Дата поступления</th>
                                <th>Отделение</th>
                                <th>Врач</th>
                                <th class="w-1"><span class="visually-hidden">Действия</span></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($maps as $map)
                                <tr>
                                    <td class="text-secondary">{{ $map->id }}</td>
                                    <td class="text-nowrap">{{ Display::date($map->admission_date) }}</td>
                                    <td>{{ $map->department?->name }}</td>
                                    <td>{{ $map->doctor?->full_name }}</td>
                                    <td>
                                        <div class="table-actions">
                                            @can('create_survey_response')
                                                <a class="btn btn-sm" href="{{ route('map.survey', $map->id) }}">
                                                    <i class="bx bx-list-check" aria-hidden="true"></i>Опросник
                                                </a>
                                            @endcan
                                            <a class="btn btn-sm btn-icon" href="{{ route('maps.show', $map->id) }}"
                                               aria-label="Карта обследования №{{ $map->id }}" title="Просмотр">
                                                <i class="bx bx-show" aria-hidden="true"></i>
                                            </a>
                                            @if ($map->is_active)
                                                @can('edit_maps')
                                                    <a class="btn btn-sm btn-icon" href="{{ route('maps.edit', $map->id) }}"
                                                       aria-label="Изменить карту обследования №{{ $map->id }}" title="Изменить">
                                                        <i class="bx bx-edit" aria-hidden="true"></i>
                                                    </a>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><x-empty-state title="Госпитализаций не было" icon="bx-clinic" /></td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- One row of buttons under every step: back, next, and saving on the last step. --}}
            <div class="d-flex justify-content-between gap-2 mt-4 pt-3 border-top">
                <button type="button" class="btn btn-prev"><i class="bx bx-chevron-left" aria-hidden="true"></i>Назад</button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-next">Далее<i class="bx bx-chevron-right ms-1" aria-hidden="true"></i></button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save" aria-hidden="true"></i>Сохранить</button>
                </div>
            </div>
        </form>
    </div>
</div>
