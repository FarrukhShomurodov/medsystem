@extends('admin.layouts.app')
@use('App\Support\Display')

@php
    $patientId = $patientDetails['patient_id'];
    $fullName = $patientDetails['basic_info']['full_name'];
    $info = $patientDetails['basic_info'];
    $searching = request()->anyFilled(['id', 'first_name', 'second_name', 'middle_name', 'birth_date']);
    $historyTitles = [
        'examination_map_start' => 'Госпитализация (начало)',
        'examination_map_end' => 'Госпитализация (конец)',
        'assignment' => 'Направление',
        'patient_med_document' => 'Мед. документ',
        'reception' => 'Приём',
        'service' => 'Услуга',
        'document' => 'Документ',
        'medication' => 'Лист назначения',
        'procedure' => 'Процедура',
        'diagnosis' => 'Диагноз',
    ];
@endphp

@section('title', $fullName)

@section('header')
    <x-page-header :title="$fullName" :breadcrumbs="['Пациенты' => auth()->user()?->can('list_patients') ? route('patients.index') : null]">
        <x-slot:actions>
            {{-- An explicit target, not url()->previous(): after a form of the card posts, the previous
                 page is the card itself. --}}
            @can('list_patients')
                @if ($activeDepartmentId !== null)
                    <a class="btn" href="{{ route('patients.index', ['department_id' => $activeDepartmentId]) }}"><i class="bx bx-arrow-back" aria-hidden="true"></i>Вернуться в отделение</a>
                @else
                    <a class="btn" href="{{ route('patients.index') }}"><i class="bx bx-arrow-back" aria-hidden="true"></i>К списку пациентов</a>
                @endif
            @endcan
            <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#generalInformationModal">
                <i class="bx bx-id-card" aria-hidden="true"></i>Общая информация
            </button>
            @can('list_patients')
                <button class="btn" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse"
                        aria-expanded="{{ $searching ? 'true' : 'false' }}" aria-controls="filtersCollapse">
                    <i class="bx bx-filter-alt" aria-hidden="true"></i>Поиск пациента
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    <div id="patient-card" class="d-flex flex-column gap-3"
         data-patient-id="{{ $patientId }}"
         data-history-url="{{ route('api.patients.history.partial') }}"
         data-substance-url="{{ route('api.medications.by-active-substance', ['substance' => '__substance__']) }}"
         data-document-url="{{ route('documents.show', ['document' => '__document__', 'patient_id' => $patientId]) }}">

        {{-- Orders: the buttons stay on screen while the history scrolls. --}}
        <div class="card patient-toolbar">
            @can('list_patients')
                <div id="filtersCollapse" class="collapse @if ($searching) show @endif">
                    <div class="card-body border-bottom">
                        @include('admin.patients.partials.filters', ['departments' => $departments, 'doctors' => null])
                    </div>
                </div>
            @endcan

            <div class="card-body">
                <div class="btn-list">
                    @can('create_patient_services')
                        <div class="btn-group" role="group" aria-label="Инструментальные исследования">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#instrumentalModal">
                                <i class="bx bx-pulse" aria-hidden="true"></i>Инструментальные
                            </button>
                            <button type="button" class="btn btn-primary btn-icon" data-bs-toggle="modal" data-bs-target="#createServiceModal"
                                    aria-label="Назначить услугу" title="Назначить услугу">
                                <i class="bx bx-plus" aria-hidden="true"></i>
                            </button>
                        </div>

                        {{-- Service type 5: "Лабораторное тестирование" (ServiceTypeSeeder). --}}
                        <div class="btn-group" role="group" aria-label="Лаборатория">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#indicativeModal">
                                <i class="bx bx-test-tube" aria-hidden="true"></i>Лаборатория
                            </button>
                            <button type="button" class="btn btn-primary btn-icon" data-bs-toggle="modal" data-bs-target="#createServiceModal"
                                    data-services-url="{{ route('api.services.by-type', 5) }}"
                                    aria-label="Назначить анализ" title="Назначить анализ">
                                <i class="bx bx-plus" aria-hidden="true"></i>
                            </button>
                        </div>
                    @endcan

                    @can('create_receptions')
                        <a class="btn btn-primary" target="_blank" href="{{ route('receptions.appointment') }}">
                            <i class="bx bx-message-dots" aria-hidden="true"></i>Консультация
                        </a>
                    @endcan

                    @canany(['list_patient_diagnoses', 'create_patient_diagnoses'])
                        <div class="btn-group" role="group" aria-label="Диагнозы">
                            @can('list_patient_diagnoses')
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#showDiagnosisesModal">
                                    <i class="bx bx-book-content" aria-hidden="true"></i>Диагноз
                                </button>
                            @endcan
                            @can('create_patient_diagnoses')
                                <button type="button" class="btn btn-primary btn-icon" data-bs-toggle="modal" data-bs-target="#diagnosesModal"
                                        aria-label="Добавить диагноз" title="Добавить диагноз">
                                    <i class="bx bx-plus" aria-hidden="true"></i>
                                </button>
                            @endcan
                        </div>
                    @endcanany

                    @canany(['list_patient_medication', 'create_patient_medication'])
                        <div class="btn-group" role="group" aria-label="Лист назначения">
                            @can('list_patient_medication')
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#showMedicationModal">
                                    <i class="bx bx-capsule" aria-hidden="true"></i>Лист назначения
                                </button>
                            @endcan
                            @can('create_patient_medication')
                                <button type="button" class="btn btn-primary btn-icon" data-bs-toggle="modal" data-bs-target="#medicationModal"
                                        aria-label="Назначить медикамент" title="Назначить медикамент">
                                    <i class="bx bx-plus" aria-hidden="true"></i>
                                </button>
                            @endcan
                        </div>
                    @endcanany

                    @can('create_patient_procedure')
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#proceduresModal">
                            <i class="bx bx-wrench" aria-hidden="true"></i>Процедуры
                        </button>
                    @endcan

                    @can('create_patient_services')
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMapModal">
                            <i class="bx bx-directions" aria-hidden="true"></i>Направление
                        </button>
                    @endcan

                    @can('create_patient_map')
                        @if ($hasActiveMap)
                            <button type="button" class="btn btn-primary" disabled title="У пациента уже есть активная карта обследования">
                                <i class="bx bx-clinic" aria-hidden="true"></i>Госпитализация
                            </button>
                        @else
                            <a class="btn btn-primary" href="{{ route('patient.map.create', $patientId) }}">
                                <i class="bx bx-clinic" aria-hidden="true"></i>Госпитализация
                            </a>
                        @endif
                    @endcan
                </div>

                @canany(['create_patient_map', 'edit_maps', 'sister_window_show'])
                    <form id="select-document-form" method="GET" class="row g-2 align-items-end mt-2">
                        <div class="col-sm-6 col-lg-4">
                            <label for="document_id" class="form-label">Документ</label>
                            <select id="document_id" name="document_id" class="form-select select2" required>
                                <option value="" disabled selected>Выберите документ</option>
                                @foreach ($documents as $document)
                                    <option value="{{ $document->id }}">{{ $document->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn"><i class="bx bx-file" aria-hidden="true"></i>Заполнить документ</button>
                        </div>
                    </form>
                @endcanany
            </div>
        </div>

        {{-- The patients this account opened last --}}
        <div class="accordion" id="accordionLastPatients">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingLastPatients">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseLastPatients" aria-expanded="false" aria-controls="collapseLastPatients">
                        Последние пациенты
                    </button>
                </h2>
                <div id="collapseLastPatients" class="accordion-collapse collapse" aria-labelledby="headingLastPatients"
                     data-bs-parent="#accordionLastPatients">
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter mb-0">
                            <thead>
                            @include('admin.patients.partials.list-head')
                            </thead>
                            <tbody>
                            @forelse ($lastViews as $viewed)
                                @include('admin.patients.partials.list-row', ['patient' => $viewed, 'number' => $loop->iteration, 'current' => $viewed->id === $patientId])
                            @empty
                                <tr><td colspan="9"><x-empty-state title="Вы ещё не открывали пациентов" icon="bx-history" /></td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion" id="warningsAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingWarnings">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseWarnings" aria-expanded="false" aria-controls="collapseWarnings">
                        Предупреждения о взаимодействии препаратов
                        @if ($warnings->isNotEmpty())
                            <span class="badge bg-danger text-white ms-2">{{ $warnings->count() }}</span>
                        @endif
                    </button>
                </h2>
                <div id="collapseWarnings" class="accordion-collapse collapse" aria-labelledby="headingWarnings"
                     data-bs-parent="#warningsAccordion">
                    <div class="accordion-body">
                        @if ($warnings->isEmpty())
                            <div class="alert alert-success mb-0">
                                <i class="bx bx-check-circle alert-icon" aria-hidden="true"></i>
                                <div>Опасные взаимодействия не обнаружены.</div>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($warnings as $warning)
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                            <div>
                                                <strong class="text-primary">{{ $warning->drug1_ru ?? $warning->drug1_en }}</strong>
                                                <span class="mx-2 text-danger">×</span>
                                                <strong class="text-primary">{{ $warning->drug2_ru ?? $warning->drug2_en }}</strong>
                                            </div>
                                            <span class="badge bg-danger text-white">{{ $warning->severity_ru ?? $warning->severity_en }}</span>
                                        </div>
                                        <div class="text-secondary small mb-1">
                                            Механизм: {{ $warning->mechanism_ru ?? $warning->mechanism_en }}
                                        </div>
                                        @if ($warning->comment_ru || $warning->comment_en)
                                            <div class="small">
                                                <i class="bx bx-info-circle text-warning me-1" aria-hidden="true"></i>
                                                {{ $warning->comment_ru ?? $warning->comment_en }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">История посещений</h3>
            </div>
            <div class="table-responsive">
                <table class="table card-table table-vcenter">
                    <thead>
                    <tr>
                        <th class="w-1">№</th>
                        <th>Записи</th>
                        <th>Физические показатели</th>
                        <th>Лист назначения</th>
                        <th>Диагноз</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($timeline as $event)
                        @php $nested = ! $event['is_main']; @endphp
                        <tr>
                            <td class="text-secondary">
                                @if ($event['is_main'])
                                    {{ $loop->iteration }}
                                @endif
                            </td>

                            <td @class(['ps-5' => $nested])>
                                <button type="button" class="btn btn-link p-0 text-start history-event"
                                        data-bs-toggle="modal" data-bs-target="#eventDetailModal"
                                        data-id="{{ $event['data']->id }}" data-type="{{ $event['type'] }}">
                                    @if ($event['type'] === 'service')
                                        <span class="fw-medium">{{ $historyTitles['service'] }}: {{ $event['data']->service?->name ?? '—' }}</span>
                                        <x-status-badge :status="$event['data']->status" class="ms-1" />
                                    @else
                                        <span class="fw-medium">{{ $historyTitles[$event['type']] ?? 'Запись' }}</span>
                                    @endif
                                </button>
                                <span class="text-secondary ms-2">{{ \Carbon\Carbon::parse($event['date'])->format('d.m.Y') }}</span>
                            </td>

                            <td>
                                @if ($event['type'] === 'reception' && $event['data']->physical)
                                    <ul class="mb-0 ps-3">
                                        @foreach ($event['data']->physical as $key => $value)
                                            <li><strong>{{ $key }}:</strong> {{ $value }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    —
                                @endif
                            </td>

                            <td>
                                @if ($event['type'] === 'assignment' && $event['data']->assignments)
                                    <ul class="mb-0 ps-3">
                                        @foreach ($event['data']->assignments as $assignment)
                                            <li>{{ $assignment }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    —
                                @endif
                            </td>

                            <td>
                                @if ($event['type'] === 'diagnosis')
                                    @php $diagnosisName = $event['data']->diagnosis?->name ?? '—'; @endphp
                                    <button type="button" class="btn btn-link p-0 text-start history-event" title="{{ $diagnosisName }}"
                                            data-bs-toggle="modal" data-bs-target="#eventDetailModal"
                                            data-id="{{ $event['data']->id }}" data-type="{{ $event['type'] }}">
                                        {{ \Illuminate\Support\Str::limit($diagnosisName, 10, '…') }}
                                    </button>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state title="Нет данных о посещениях" icon="bx-calendar" /></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- The dashboard's registrar queue links here (#registration). --}}
        <div class="card" id="registration">
            <div class="card-header">
                <h3 class="card-title">Регистрация: услуги, приёмы и процедуры</h3>
                <div class="card-actions">
                    <a href="{{ route('registrar.export', $patientId) }}" class="btn">
                        <i class="bx bx-download" aria-hidden="true"></i>Экспорт в Excel
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table card-table table-vcenter">
                    <thead>
                    <tr>
                        <th class="w-1">#</th>
                        <th>Тип</th>
                        <th>Название</th>
                        <th>Цена</th>
                        <th>Статус</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($allRecords as $i => $item)
                        <tr>
                            <td class="text-secondary">{{ $i + 1 }}</td>
                            <td><x-status-badge :status="$item->type" /></td>
                            <td>{{ $item->name }}</td>
                            <td class="text-nowrap">{{ $item->price->format() }} {{ $item->currency }}</td>
                            <td><x-status-badge :status="$item->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state title="Нечего подтверждать" text="Назначенные услуги, приёмы и процедуры появятся здесь." icon="bx-receipt" /></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="fs-3 fw-bold">Итого: {{ $total }}</div>
                @can('confirm_patient_registration')
                    <form method="POST" action="{{ route('registrar.confirm', $patientId) }}">
                        @csrf
                        <button type="submit" class="btn btn-success"><i class="bx bx-check" aria-hidden="true"></i>Подтвердить</button>
                    </form>
                @endcan
            </div>
        </div>

        <div class="accordion" id="patient-info">
            <div class="accordion-item">
                <h2 class="accordion-header" id="patient-info-general-heading">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#patient-info-general" aria-expanded="false" aria-controls="patient-info-general">
                        Общая информация
                    </button>
                </h2>
                <div id="patient-info-general" class="accordion-collapse collapse" aria-labelledby="patient-info-general-heading"
                     data-bs-parent="#patient-info">
                    <div class="accordion-body">
                        @include('admin.patients.partials.general-info', ['info' => $info])
                    </div>
                </div>
            </div>

            @foreach ([
                'contacts' => ['Контактная информация', [
                    'Телефон' => $patientDetails['contact_info']['phone'],
                    'Доп. телефон' => $patientDetails['contact_info']['additional_phone'],
                    'Email' => $patientDetails['contact_info']['email'],
                    'Адрес проживания' => $patientDetails['contact_info']['residential_address'],
                    'Адрес регистрации' => $patientDetails['contact_info']['registered_address'],
                ]],
                'insurance' => ['Страховая информация', [
                    'Номер полиса' => $patientDetails['insurance_info']['policy_number'],
                    'Компания' => $patientDetails['insurance_info']['company'],
                    'Тип' => $patientDetails['insurance_info']['type'],
                    'Срок действия' => filled($patientDetails['insurance_info']['expiry_date']) ? Display::date($patientDetails['insurance_info']['expiry_date']) : 'Не указана',
                ]],
                'emergency' => ['Экстренный контакт', [
                    'ФИО' => $patientDetails['emergency_contact']['full_name'],
                    'Отношение' => $patientDetails['emergency_contact']['relation'],
                    'Телефон' => $patientDetails['emergency_contact']['phone'],
                    'Адрес' => $patientDetails['emergency_contact']['address'],
                ]],
            ] as $section => [$sectionTitle, $rows])
                <div class="accordion-item">
                    <h2 class="accordion-header" id="patient-info-{{ $section }}-heading">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#patient-info-{{ $section }}" aria-expanded="false" aria-controls="patient-info-{{ $section }}">
                            {{ $sectionTitle }}
                        </button>
                    </h2>
                    <div id="patient-info-{{ $section }}" class="accordion-collapse collapse"
                         aria-labelledby="patient-info-{{ $section }}-heading" data-bs-parent="#patient-info">
                        <div class="accordion-body">
                            <div class="datagrid">
                                @foreach ($rows as $rowLabel => $rowValue)
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">{{ $rowLabel }}</div>
                                        <div class="datagrid-content">{{ filled($rowValue) ? $rowValue : '—' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            @can('edit_patients')
                <div class="accordion-item">
                    <h2 class="accordion-header" id="patient-info-norms-heading">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#patient-info-norms" aria-expanded="false" aria-controls="patient-info-norms">
                            Нормальные показатели пациента
                        </button>
                    </h2>
                    <div id="patient-info-norms" class="accordion-collapse collapse" aria-labelledby="patient-info-norms-heading"
                         data-bs-parent="#patient-info">
                        <div class="accordion-body">
                            <form action="{{ route('patient.normal.vital.parameter.store', $patientId) }}" method="POST">
                                @csrf
                                <div class="row g-3">
                                    @foreach ($vitalParameters as $param)
                                        <div class="col-sm-6 col-lg-4">
                                            <label class="form-label" for="norm-{{ $param->id }}">{{ $param->name }}</label>
                                            <input type="number" step="0.1" id="norm-{{ $param->id }}" name="parameters[{{ $param->id }}]"
                                                   class="form-control @error('parameters.'.$param->id) is-invalid @enderror" placeholder="Норма"
                                                   value="{{ old('parameters.'.$param->id, $normalVitalValues->get($param->id, '')) }}">
                                            @error('parameters.'.$param->id)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    @endforeach
                                </div>
                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-primary">Сохранить</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endcan
        </div>
    </div>

    <div class="modal modal-blur fade" id="generalInformationModal" tabindex="-1" aria-labelledby="generalInformationModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="generalInformationModalTitle">Общая информация</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    @include('admin.patients.partials.general-info', ['info' => $info])
                </div>
            </div>
        </div>
    </div>

    {{-- The detail of a history row, loaded from api.patients.history.partial --}}
    <div class="modal modal-blur fade" id="eventDetailModal" tabindex="-1" aria-labelledby="eventDetailModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="eventDetailModalTitle">Детали события</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body" aria-live="polite"></div>
            </div>
        </div>
    </div>

    @can('create_patient_services')
        {{-- Results of the studies: not recorded by the system yet. --}}
        @foreach (['instrumentalModal' => 'Инструментальные исследования', 'indicativeModal' => 'Результаты медицинских анализов'] as $modalId => $modalTitle)
            <div class="modal modal-blur fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Title" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title" id="{{ $modalId }}Title">{{ $modalTitle }}</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                        </div>
                        <div class="modal-body">
                            <x-empty-state title="Результатов пока нет" icon="bx-test-tube"
                                           text="Результаты исследований пациента в системе пока не хранятся." />
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Referral (направление) to a service --}}
        <div class="modal modal-blur fade" id="createMapModal" tabindex="-1" aria-labelledby="createMapModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="createMapModalTitle">Создание направления</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <form action="{{ route('patient.assignment.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="patient_id" value="{{ $patientId }}">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="service" class="form-label required">Услуга</label>
                                <select id="service" class="form-select select2" name="service_id" data-placeholder="Выберите услугу" required>
                                    <option value="" disabled selected>Выберите услугу</option>
                                    @foreach ($patientDetails['services'] as $service)
                                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="comment" class="form-label">Комментарий</label>
                                <textarea name="comment" class="form-control" id="comment" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn" data-bs-dismiss="modal">Закрыть</button>
                            <button type="submit" class="btn btn-primary">Создать направление</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Order a service --}}
        <div class="modal modal-blur fade" id="createServiceModal" tabindex="-1" aria-labelledby="createServiceModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="createServiceModalTitle">Назначение услуги</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <form action="{{ route('patient.services.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="patient_id" value="{{ $patientId }}">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="service_create" class="form-label required">Услуга</label>
                                    <select id="service_create" class="form-select select2" name="service_id" required>
                                        <option value="" disabled selected>Выберите услугу</option>
                                        @foreach ($patientDetails['services'] as $service)
                                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label" for="service_date">Дата услуги</label>
                                    <input type="text" name="service_date" id="service_date" placeholder="ГГГГ-ММ-ДД"
                                           class="form-control flatpickr-date @error('service_date') is-invalid @enderror"
                                           value="{{ old('service_date') }}">
                                    @error('service_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn" data-bs-dismiss="modal">Закрыть</button>
                            <button type="submit" class="btn btn-primary">Создать</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    {{-- A medical document for the patient (or for one of the patient's services): response.survey.store --}}
    @can('store_survey_response')
        <div class="modal modal-blur fade" id="createDocumentModal" tabindex="-1" aria-labelledby="createDocumentModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="createDocumentModalTitle">Мед. документ</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <form action="{{ route('response.survey.store') }}" method="POST" class="modal-form">
                        @csrf
                        <input type="hidden" name="patient_id" value="{{ $patientId }}">
                        <input type="hidden" name="patient_service_id" id="modal_service_id">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label required" for="survey-select">Мед. документ</label>
                                <select id="survey-select" name="survey_id" required
                                        class="form-select select2 @error('survey_id') is-invalid @enderror"
                                        data-questions-url="{{ route('api.surveys.questions', ['survey' => '__survey__']) }}">
                                    <option value="" disabled selected>Выберите документ</option>
                                    @foreach ($patientDetails['surveys'] as $survey)
                                        <option value="{{ $survey->id }}" @selected($survey->id == old('survey_id'))>{{ $survey->title }}</option>
                                    @endforeach
                                </select>
                                @error('survey_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div id="questions-container"
                                 data-choice-type="{{ \App\Enums\QuestionType::Choice->value }}"
                                 data-boolean-type="{{ \App\Enums\QuestionType::Boolean->value }}"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn" data-bs-dismiss="modal">Закрыть</button>
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    @can('create_patient_diagnoses')
        <div class="modal modal-blur fade" id="diagnosesModal" tabindex="-1" aria-labelledby="diagnosesModalTitle" aria-hidden="true"
             data-store-url="{{ route('patient.diagnoses.store') }}"
             data-favorite-url="{{ route('api.selected-diagnoses.toggle') }}"
             data-search-url="{{ route('api.diagnoses.search') }}">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="diagnosesModalTitle">Добавить диагноз</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        <div class="input-icon mb-3">
                            <span class="input-icon-addon"><i class="bx bx-search" aria-hidden="true"></i></span>
                            <input type="search" class="form-control" id="diagnosisSearchInput" placeholder="Поиск диагноза…"
                                   aria-label="Поиск диагноза">
                        </div>

                        <section class="diagnosis-section mb-3 @if ($favoriteDiagnoses->isEmpty()) d-none @endif" data-list="favorite">
                            <h3 class="subheader mb-2">Избранные диагнозы</h3>
                            <div class="list-group" id="favoriteDiagnosisList">
                                @foreach ($favoriteDiagnoses as $diagnosis)
                                    @include('admin.patients.partials.diagnosis-option', ['diagnosis' => $diagnosis, 'favorite' => true])
                                @endforeach
                            </div>
                        </section>

                        {{-- The rest of the ICD-10 catalogue is searched as the user types (api.diagnoses.search). --}}
                        <section class="diagnosis-section mb-3" data-list="regular">
                            <h3 class="subheader mb-2">Все диагнозы</h3>
                            <p class="text-secondary mb-2" id="diagnosisSearchStatus" role="status">Введите код или название диагноза.</p>
                            <div class="list-group" id="regularDiagnosisList"></div>
                        </section>

                        <template id="diagnosis-option-template">
                            @include('admin.patients.partials.diagnosis-option', ['diagnosis' => null, 'favorite' => false])
                        </template>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn" data-bs-dismiss="modal">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    @can('list_patient_diagnoses')
        <div class="modal modal-blur fade" id="showDiagnosisesModal" tabindex="-1" aria-labelledby="showDiagnosisesModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="showDiagnosisesModalTitle">Диагнозы пациента</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        @forelse ($patientDetails['diagnoses'] as $data)
                            @include('admin.patients.history.diagnosis', ['data' => $data])
                        @empty
                            <x-empty-state title="Диагнозов пока нет" icon="bx-book-content" />
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endcan

    @can('create_patient_medication')
        <div class="modal modal-blur fade" id="medicationModal" tabindex="-1" aria-labelledby="medicationModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="medicationModalTitle">Назначить медикаменты</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <form action="{{ route('patient.medication.store') }}" method="POST" class="modal-form" id="medication-form">
                        @csrf
                        <input type="hidden" name="patient_id" value="{{ $patientId }}">
                        <div class="modal-body">
                            <div id="medications-container"></div>
                            <x-empty-state class="medications-empty" title="Добавьте медикамент" icon="bx-capsule"
                                           text="Каждый медикамент назначается с дозировкой, временем и правилами приёма." />
                            <div class="text-end">
                                <button type="button" class="btn btn-primary" id="btn-add-medication">
                                    <i class="bx bx-plus" aria-hidden="true"></i>Добавить медикамент
                                </button>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn" data-bs-dismiss="modal">Закрыть</button>
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <template id="medication-template">
            <div class="card mb-3 prescription-row medication-row">
                <div class="card-header">
                    <h3 class="card-title medication-title">Медикамент</h3>
                    <div class="card-actions">
                        <button type="button" class="btn btn-sm btn-ghost-danger btn-remove-medication">
                            <i class="bx bx-trash" aria-hidden="true"></i>Убрать
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label required" data-for="medication">Основной медикамент</label>
                            <select name="medications[]" class="form-select medication-select" data-id="medication" required>
                                @foreach ($medications as $medication)
                                    <option value="{{ $medication->id }}"
                                            data-active-substance="{{ $medication->composition?->active_substance_name }}">
                                        {{ $medication->name }} ({{ $medication->composition?->active_substance_name ?? '—' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required" data-for="dosage">Дозировка (количество единиц)</label>
                            <input type="number" name="dosages[]" class="form-control" data-id="dosage"
                                   min="1" max="10000" step="1" placeholder="Количество единиц" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required" data-for="single-dose">Разовая доза</label>
                            <input type="text" name="single_doses[]" class="form-control" data-id="single-dose"
                                   placeholder="Разовая доза" required maxlength="50">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required" data-for="date-of-end">Дата</label>
                            <input type="text" name="date_of_ends[]" class="form-control flatpickr-date" data-id="date-of-end"
                                   placeholder="ГГГГ-ММ-ДД" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required" data-for="method">Способ приёма</label>
                            <select name="methods[]" class="form-select" data-id="method" required>
                                @foreach (\App\Enums\AdministrationRoute::options() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required" data-for="admission-rule">Правило приёма</label>
                            <select name="admission_rules[]" class="form-select" data-id="admission-rule" required>
                                @foreach (\App\Enums\AdmissionRule::options() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label required" data-for="notes">Примечания</label>
                            <textarea name="medication_notes[]" class="form-control" data-id="notes" rows="2"
                                      placeholder="Примечания" required maxlength="255"></textarea>
                        </div>
                        <div class="col-12">
                            @include('admin.patients.partials.admission-times', ['times' => ''])
                        </div>
                        <div class="col-12">
                            @include('admin.patients.partials.compatible-medications', ['compatible' => collect()])
                        </div>
                    </div>
                </div>
            </div>
        </template>
    @endcan

    @can('list_patient_medication')
        <div class="modal modal-blur fade" id="showMedicationModal" tabindex="-1" aria-labelledby="showMedicationModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="showMedicationModalTitle">Лист назначения</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter">
                                <thead>
                                <tr>
                                    <th class="w-1">#</th>
                                    <th>Дата и время назначения</th>
                                    <th>Препарат, доза, правило и время приёма (ч:мин)</th>
                                    <th>Дата</th>
                                    <th>Статус</th>
                                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse ($prescriptions as $index => $medication)
                                    <tr>
                                        <td class="text-secondary">{{ $index + 1 }}</td>
                                        <td class="text-nowrap">{{ Display::dateTime($medication->pivot->created_at) }}</td>
                                        <td>
                                            {{ $medication->name }}, {{ $medication->pivot->dosage }}, {{ $medication->pivot->admission_rules->label() }}
                                            <div class="tag-list mt-1">
                                                @foreach (array_filter(explode(',', (string) $medication->pivot->admission_period)) as $time)
                                                    <span class="badge bg-primary-lt">{{ $time }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="text-nowrap">{{ Display::date($medication->pivot->date_of_end) }}</td>
                                        <td class="text-nowrap">
                                            <x-status-icon :status="$medication->pivot->status" decorative />
                                            {{ $medication->pivot->status->label() }}
                                        </td>
                                        <td>
                                            {{-- The three routes need edit_patient_medication and refuse a given, returned or cancelled prescription. --}}
                                            @can('edit_patient_medication')
                                                @unless ($medication->pivot->status->isFinal())
                                                    <div class="table-actions">
                                                        <button type="button" class="btn btn-sm js-extend-medication"
                                                                data-url="{{ route('api.patient-medications.extend', $medication->pivot->id) }}">
                                                            Продлить
                                                        </button>
                                                        <a href="{{ route('patient.medication.edit', $medication->pivot->id) }}" class="btn btn-sm">Изменить</a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger js-cancel-medication"
                                                                data-url="{{ route('api.patient-medications.cancel', $medication->pivot->id) }}">
                                                            Отменить
                                                        </button>
                                                    </div>
                                                @endunless
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6"><x-empty-state title="Медикаменты не назначены" icon="bx-capsule" /></td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    @can('create_patient_procedure')
        <div class="modal modal-blur fade" id="proceduresModal" tabindex="-1" aria-labelledby="proceduresModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="proceduresModalTitle">Назначить процедуры</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <form action="{{ route('patient.procedures.store') }}" method="POST" class="modal-form">
                        @csrf
                        <input type="hidden" name="patient_id" value="{{ $patientId }}">
                        <div class="modal-body">
                            <div id="procedures-container" class="d-flex flex-column gap-2 mb-3"></div>
                            <button type="button" class="btn btn-primary" id="btn-add-procedure">
                                <i class="bx bx-plus" aria-hidden="true"></i>Добавить процедуру
                            </button>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn" data-bs-dismiss="modal">Закрыть</button>
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <template id="procedure-template">
            <div class="row g-2 align-items-end procedure-row">
                <div class="col-sm-7">
                    <label class="form-label required" data-for="procedure">Процедура</label>
                    <select name="procedures[]" class="form-select select2 procedure-select" data-id="procedure"
                            data-placeholder="Выберите процедуру" required>
                        <option value=""></option>
                        @foreach ($procedures as $procedure)
                            <option value="{{ $procedure->id }}">{{ $procedure->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4">
                    <label class="form-label required" data-for="procedure-date">Дата</label>
                    <input type="text" name="procedure_dates[]" class="form-control flatpickr-date" data-id="procedure-date"
                           value="{{ now()->format('Y-m-d') }}" required>
                </div>
                <div class="col-sm-1">
                    <button type="button" class="btn btn-icon btn-ghost-danger btn-remove-procedure" aria-label="Убрать процедуру" title="Убрать процедуру">
                        <i class="bx bx-trash" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </template>
    @endcan
@endsection

@section('scripts')
    @vite('resources/js/pages/patient-card.js')
@endsection
