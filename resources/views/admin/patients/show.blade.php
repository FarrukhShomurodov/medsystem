@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Пациент</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('patients.index') }}">Пациент</a> /</span> {{ $patientDetails['basic_info']['full_name'] }}
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

        @if(session()->has('success'))
            <div class="alert alert-solid-success alert-dismissible d-flex align-items-center" role="alert">
                <i class="bx bx-check-circle fs-4 me-2"></i>
                {{ session()->get('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Последние пациенты</h5>
        </div>
        <table class="datatables-users table border-top">
            <thead>
            <tr>
                <th>#</th>
                <th>Дата поступления</th>
                <th>Отделения</th>
                <th>ФИО</th>
                <th>Дата рождения / Возвраст</th>
                <th>Палаты</th>
                <th>Диагноз</th>
                <th>Дата и название операции</th>
                <th>Лечащий врач</th>
                <th>Дней в стационаре</th>
                <th></th>
            </tr>
            </thead>
            <tbody>

            @php
                $count = 1;
            @endphp

            @foreach($lastViews as $patient)
                <tr class="cursor-pointer">
                    <td onclick="location.href='{{ route('patients.show', $patient->id) }}'">{{ $count++ }}</td>
                    <td onclick="location.href='{{ route('patients.show', $patient->id) }}'">{{ $patient->created_at }}</td>
                    <td onclick="location.href='{{ route('patients.show', $patient->id) }}'">
                        {{ $patient->examinationMaps()->first()?->department->name ?? '-' }}
                    </td>
                    <td onclick="location.href='{{ route('patients.show', $patient->id) }}'">
                        {{ $patient->first_name }} {{ $patient->last_name }} {{ $patient->middle_name }}
                    </td>
                    <td onclick="location.href='{{ route('patients.show', $patient->id) }}'">{{ $patient->birth_date }}
                        / {{ \Carbon\Carbon::parse($patient->birth_date)->age }}</td>
                    <td onclick="location.href='{{ route('patients.show', $patient->id) }}'">-</td>
                    <td onclick="location.href='{{ route('patients.show', $patient->id) }}'">{{ $patient->examinationMaps()->first()?->admission_diagnosis_description ?? '-' }}</td>
                    <td onclick="location.href='{{ route('patients.show', $patient->id) }}'">-</td>
                    <td onclick="location.href='{{ route('patients.show', $patient->id) }}'">{{ $patient->examinationMaps()->first()?->doctor->name ?? '-' }}</td>
                    <td onclick="location.href='{{ route('patients.show', $patient->id) }}'">{{ \Carbon\Carbon::parse($patient->examinationMaps()->first()?->admission_date)->age }}</td>

                    <td>
                        <div class="d-inline-block text-nowrap">
                            <button class="btn btn-sm btn-warning"
                                    onclick="location.href='{{ route('patients.edit', $patient->id) }}'">
                                Редактировать
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card mt-3">
        <div>
            <h5 class="card-header">История посещений</h5>
        </div>

        <div class="d-flex justify-content-start align-items-start mb-3" style="margin-left: 1.375rem">
            <div>
                <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#instrumentalModal">
                    Инструментальные исследования
                </button>

                <button
                    type="button"
                    class="btn btn-primary me-3"
                    data-bs-toggle="modal"
                    data-bs-target="#createServiceModal">
                    +
                </button>
            </div>

            <div>
                <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-type-id="5"
                    data-bs-target="#indicativeModal">
                    Лаборатория
                </button>

                <button
                    type="button"
                    class="btn btn-primary me-3"
                    data-bs-toggle="modal"
                    data-type-id="5"
                    data-bs-target="#createServiceModal">
                    +
                </button>
            </div>


            <a
                type="button"
                class="btn btn-primary me-3"
                target="_blank"
                href="{{ route('receptions.appointment') }}">
                Консультация
            </a>

            <div>
                <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#showDiagnosisesModal">
                    Диагноз
                </button>

                <button
                    type="button"
                    class="btn btn-primary me-3"
                    data-bs-toggle="modal"
                    data-bs-target="#diagnosisesModal">
                    +
                </button>
            </div>


            <button
                type="button"
                class="btn btn-primary me-3"
                data-bs-toggle="modal"
                data-bs-target="#medicationModal">
                Медикамент
            </button>

            {{--                <button--}}
            {{--                    type="button"--}}
            {{--                    class="btn btn-primary me-3"--}}
            {{--                    data-bs-toggle="modal"--}}
            {{--                    data-bs-target="#createDocumentModal">--}}
            {{--                    Мед Документ--}}
            {{--                </button>--}}

            {{--                <button--}}
            {{--                    type="button"--}}
            {{--                    class="btn btn-primary me-3"--}}
            {{--                    data-bs-toggle="modal"--}}
            {{--                    data-bs-target="#createMapModal">--}}
            {{--                    Направление--}}
            {{--                </button>--}}

            <button
                onclick="window.location.href='{{ route('patient.map.create', $patientDetails['patient_id']) }}'"
                class="btn btn-primary me-3"
                @disabled(!$patientDetails['history']['map_is_active'])>Создать госпитализацию
            </button>
        </div>

        <div>
            <div class="ms-4">
                <form id="select-document-form" method="GET" action="">
                    <div class="row g-3">
                        <div class="col-md-2 form-group">
                            <label for="document_id">Выберите документ</label>
                            <select id="document_id" name="document_id" class="form-control select2">
                                <option disabled selected>Выберите</option>
                                @foreach($documents as $document)
                                    <option value="{{ $document->id }}">
                                        {{ $document->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 form-group">
                            <input type="hidden" id="patient_id" name="patient_id"
                                   value="{{ $patientDetails['patient_id'] }}">


                            <label class="form-label" for="basic-icon-birth-date"></label>
                            <div class="input-group input-group-merge">
                                <button type="submit" class="btn btn-primary">Добавить документ</button>
                            </div>
                        </div>
                    </div>


                </form>
            </div>
        </div>

        <div class="card-body d-flex justify-content-between align-items-center">
            <form method="GET" action="{{ route('patients.index') }}">
                <div class="row g-3 align-items-center">
                    <div class="col-md-2 form-group">
                        <label class="form-label" for="id">ID</label>
                        <div class="input-group input-group-merge">
                            <input type="text" name="id" value="{{ request("id") }}"
                                   id="id"
                                   class="form-control"
                                   placeholder="ID"/>
                        </div>
                    </div>

                    <div class="col-md-2 form-group">
                        <label class="form-label" for="first_name">Имя</label>
                        <div class="input-group input-group-merge">
                            <input type="text" name="first_name" value="{{ request("first_name") }}"
                                   id="first_name"
                                   class="form-control"
                                   placeholder="Имя"/>
                        </div>
                    </div>

                    <div class="col-md-2 form-group">
                        <label class="form-label" for="second_name">Фамилия</label>
                        <div class="input-group input-group-merge">
                            <input type="text" name="second_name" value="{{ request("second_name") }}"
                                   id="second_name"
                                   class="form-control"
                                   placeholder="Фамилия"/>
                        </div>
                    </div>

                    <div class="col-md-2 form-group">
                        <label class="form-label" for="middle_name">Отчество</label>
                        <div class="input-group input-group-merge">
                            <input type="text" name="middle_name" value="{{ request("middle_name") }}"
                                   id="middle_name"
                                   class="form-control"
                                   placeholder="Отчество"/>
                        </div>
                    </div>

                    <div class="col-md-2 form-group">
                        <label class="form-label" for="basic-icon-birth-date">Дата рождения </label>
                        <div class="input-group input-group-merge">
                                    <span id="basic-icon-birth-date2" class="input-group-text"><i
                                            class="bx bx-calendar"></i></span>
                            <input type="text" name="birth_date" value="{{ request("birth_date") }}"
                                   id="basic-icon-birth-date"
                                   class="form-control flatpickr-date"
                                   placeholder="YYYY-MM-DD" aria-describedby="basic-icon-birth-date2"/>
                        </div>
                    </div>

                    @php
                        $filters = [
                            ['id' => 'department', 'label' => 'Отделения', 'items' => $departments],
                        ];
                    @endphp

                    @foreach($filters as $filter)
                        <div class="col-md-2 form-group">
                            <label for="{{ $filter['id'] }}">{{ $filter['label'] }}</label>
                            <select id="{{ $filter['id'] }}" name="{{ $filter['id'] }}_id"
                                    class="form-control select2">
                                <option value="">Все</option>
                                @foreach($filter['items'] as $item)
                                    <option value="{{ $item->id }}"
                                        @selected(request("{$filter['id']}_id") == $item->id)>
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach

                    <div class="w-100">
                        <label class="form-label" for="basic-icon-birth-date"></label>
                        <div class="d-flex flex-row justify-content-between align-items-center w-100">
                            <button type="submit" class="btn btn-primary">Поиск</button>

                            <a class="btn btn-primary" href="{{ url()->previous() }}">
                                Вернуться в отделение
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>№</th>
                        <th>Записи</th>
                        <th>Физические показатели</th>
                        <th>Лист назначения</th>
                        <th>Диагноз</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $groupedTimeline = collect();

                        foreach ($patientDetails['timeline'] as $event) {
                            $item = [
                                'type' => $event['type'],
                                'date' => $event['date'],
                                'data' => $event['data'],
                                'is_main' => true,
                                'parent_id' => null
                            ];

                            // Если это госпитализация, добавляем ее первой
                            if ($event['type'] === 'examination_map') {
                                  $groupedTimeline->push([
                                        'type'       => 'examination_map_start',
                                        'date'       => $event['date'],
                                        'data'       => $event['data'],
                                        'is_main'    => true,
                                        'parent_id'  => null,
                                    ]);

                                 if ($event['end_date']) {
                                    $groupedTimeline->push([
                                        'type'       => 'examination_map_end',
                                        'date'       => $event['end_date'],
                                        'data'       => $event['data'],
                                        'is_main'    => true,
                                        'parent_id'  => null,
                                    ]);
                                }

                                // Добавляем связанные элементы как дочерние
                                foreach ($event['data']->services as $service) {
                                    $groupedTimeline->push([
                                        'type' => 'service',
                                        'date' => $service->created_at,
                                        'data' => $service,
                                        'is_main' => false,
                                        'parent_id' => $event['data']->id
                                    ]);
                                }

                                foreach ($event['data']->diagnosises as $diagnosis) {
                                    $groupedTimeline->push([
                                        'type' => 'diagnosis',
                                        'date' => $diagnosis->created_at,
                                        'data' => $diagnosis,
                                        'is_main' => false,
                                        'parent_id' => $event['data']->id
                                    ]);
                                }

                                foreach ($event['data']->receptions as $reception) {
                                    $groupedTimeline->push([
                                        'type' => 'reception',
                                        'date' => $reception->created_at,
                                        'data' => $reception,
                                        'is_main' => false,
                                        'parent_id' => $event['data']->id
                                    ]);
                                }

                                foreach ($event['data']->surveyResponses as $response) {
                                    $groupedTimeline->push([
                                        'type' => 'patient_med_document',
                                        'date' => $response->created_at,
                                        'data' => $response,
                                        'is_main' => false,
                                        'parent_id' => $event['data']->id
                                    ]);
                                }

                              foreach ($event['data']->documentResponse as $response) {
                                $groupedTimeline->push([
                                    'type' => 'document',
                                    'date' => $response->created_at,
                                    'data' => $response,
                                    'is_main' => false,
                                    'parent_id' => $event['data']->id
                                ]);
                                }
                            } else {
                                // Добавляем независимые элементы
                                $groupedTimeline->push($item);
                            }
                        }

                        // Сортируем по дате (главные элементы первыми)
                        $groupedTimeline = $groupedTimeline->sortByDesc('date');
                    @endphp

                    @forelse($groupedTimeline as $index => $event)
                        <tr class="@if(!$event['is_main'] || $event['type'] == 'examination_map_start' || $event['type'] == 'examination_map_end') nested-item @endif">
                            <td>
                                @if($event['is_main'])
                                    {{ $loop->iteration }}
                                @endif
                            </td>

                            <!-- Записи -->
                            <td>
                                <div class="history-item"
                                     @if(!$event['is_main'] || $event['type'] !== 'examination_map_start' || $event['type'] !== 'examination_map_end') style="margin-left: 20px;" @endif>
                                    <div class="history-header"
                                         data-bs-toggle="modal"
                                         data-bs-target="#eventDetailModal"
                                         data-id='{{ $event['data']->id }}'
                                         data-type='{{ $event['type'] }}'>
                                        <div>
                                <span class="history-title">
                                    @switch($event['type'])
                                        @case('examination_map_start')
                                            Госпитализация (начало)
                                            @break
                                        @case('examination_map_end')
                                            Госпитализация (конец)
                                            @break
                                        @case('assignment')
                                            Направление
                                            @break
                                        @case('service')
                                            <Услуга></Услуга>
                                            @break
                                        @case('patient_med_document')
                                            Мед. Документ
                                            @break
                                        @case('reception')
                                            Прием
                                            @break
                                        @case('document')
                                            Документ
                                            @break
                                        @case('medication')
                                            Медикамент
                                            @break
                                        @case('diagnosis')

                                            @break
                                    @endswitch
                                </span>
                                            <span class="history-date">
                                    {{ \Carbon\Carbon::parse($event['date'])->format('d.m.Y') }}
                                </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Физические показатели -->
                            <td>
                                @if($event['type'] === 'reception' && $event['data']->physical)
                                    <ul class="mb-0">
                                        @foreach($event['data']->physical as $key => $value)
                                            <li><strong>{{ $key }}:</strong> {{ $value }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    —
                                @endif
                            </td>

                            <!-- Лист назначения -->
                            <td>
                                @if($event['type'] === 'assignment' && $event['data']->assignments)
                                    <ul class="mb-0">
                                        @foreach($event['data']->assignments as $assignment)
                                            <li>{{ $assignment }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    —
                                @endif
                            </td>

                            <!-- Диагноз -->
                            <td>
                                @if($event['type'] == 'diagnosis')
                                    <div class="history-item" @if(!$event['is_main']) style="margin-left: 20px;" @endif>
                                        <div class="history-header"
                                             data-bs-toggle="modal"
                                             data-bs-target="#eventDetailModal"
                                             data-id='{{ $event['data']->id }}'
                                             data-type='{{ $event['type'] }}'>
                                            <div>
                                                <span class="history-title">
                                                    {{ \Illuminate\Support\Str::limit($event['data']->diagnosis->name, 10, '...') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Нет данных о посещениях</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @php $accordionId = uniqid('accordion_'); @endphp

    <div class="accordion mt-3" id="{{ $accordionId }}">
        {{-- Контактная информация --}}
        <div class="accordion-item">
            <h2 class="accordion-header" id="{{ $accordionId }}-headingOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#{{ $accordionId }}-collapseOne" aria-expanded="false"
                        aria-controls="{{ $accordionId }}-collapseOne">
                    Контактная информация
                </button>
            </h2>
            <div id="{{ $accordionId }}-collapseOne" class="accordion-collapse collapse"
                 aria-labelledby="{{ $accordionId }}-headingOne" data-bs-parent="#{{ $accordionId }}">
                <div class="accordion-body">
                    <p><strong>Телефон:</strong> {{ $patientDetails['contact_info']['phone'] }}</p>
                    <p><strong>Доп. телефон:</strong> {{ $patientDetails['contact_info']['additional_phone'] }}</p>
                    <p><strong>Email:</strong> {{ $patientDetails['contact_info']['email'] }}</p>
                    <p><strong>Адрес проживания:</strong> {{ $patientDetails['contact_info']['residential_address'] }}
                    </p>
                    <p><strong>Адрес регистрации:</strong> {{ $patientDetails['contact_info']['registered_address'] }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Страховая информация --}}
        <div class="accordion-item mt-3">
            <h2 class="accordion-header" id="{{ $accordionId }}-headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#{{ $accordionId }}-collapseTwo" aria-expanded="false"
                        aria-controls="{{ $accordionId }}-collapseTwo">
                    Страховая информация
                </button>
            </h2>
            <div id="{{ $accordionId }}-collapseTwo" class="accordion-collapse collapse"
                 aria-labelledby="{{ $accordionId }}-headingTwo" data-bs-parent="#{{ $accordionId }}">
                <div class="accordion-body">
                    <p><strong>Номер полиса:</strong> {{ $patientDetails['insurance_info']['policy_number'] }}</p>
                    <p><strong>Компания:</strong> {{ $patientDetails['insurance_info']['company'] }}</p>
                    <p><strong>Тип:</strong> {{ $patientDetails['insurance_info']['type'] }}</p>
                    <p><strong>Срок действия:</strong> {{ $patientDetails['insurance_info']['expiry_date'] }}</p>
                </div>
            </div>
        </div>

        {{-- Экстренный контакт --}}
        <div class="accordion-item mt-3">
            <h2 class="accordion-header" id="{{ $accordionId }}-headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#{{ $accordionId }}-collapseThree" aria-expanded="false"
                        aria-controls="{{ $accordionId }}-collapseThree">
                    Экстренный контакт
                </button>
            </h2>
            <div id="{{ $accordionId }}-collapseThree" class="accordion-collapse collapse"
                 aria-labelledby="{{ $accordionId }}-headingThree" data-bs-parent="#{{ $accordionId }}">
                <div class="accordion-body">
                    <p><strong>ФИО:</strong> {{ $patientDetails['emergency_contact']['full_name'] }}</p>
                    <p><strong>Отношение:</strong> {{ $patientDetails['emergency_contact']['relation'] }}</p>
                    <p><strong>Телефон:</strong> {{ $patientDetails['emergency_contact']['phone'] }}</p>
                    <p><strong>Адрес:</strong> {{ $patientDetails['emergency_contact']['address'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="accordion mt-3" id="patientInfoAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingInfo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseInfo" aria-expanded="false" aria-controls="collapseInfo">
                    Общая информация
                </button>
            </h2>
            <div id="collapseInfo" class="accordion-collapse collapse" aria-labelledby="headingInfo"
                 data-bs-parent="#patientInfoAccordion">
                <div class="accordion-body">
                    <p><strong>ФИО:</strong> {{ $patientDetails['basic_info']['full_name'] }}</p>
                    <p><strong>Дата рождения:</strong> {{ $patientDetails['basic_info']['birth_date'] }}</p>
                    <p><strong>Пол:</strong> {{ $patientDetails['basic_info']['gender'] }}</p>
                    <p><strong>Гражданство:</strong> {{ $patientDetails['basic_info']['citizenship'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for creating direction -->
    <div class="modal fade" id="createMapModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Создание направления</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('patient.assignment.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="patient_id" value="{{ $patientDetails['patient_id'] }}">
                            <div class="col-12 mb-3">
                                <label for="service" class="form-label">Услуга</label>
                                <select id="service" class="form-control select2" name="service_id" required>
                                    <option disabled selected>Выберите услугу</option>
                                    @foreach($patientDetails['services'] as $service)
                                        <option value="{{$service->id}}">{{ $service->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="comment" class="form-label">Коментарий</label>
                                <textarea name="comment" class="form-control" id="comment"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Закрыть</button>
                        <button type="submit" class="btn btn-primary">Создать направление</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Assign Serice --}}
    <div class="modal fade" id="createServiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Назначение услуги</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('patient.services.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="patient_id" value="{{ $patientDetails['patient_id'] }}">
                            <div class="col-sm-6 mb-3">
                                <label for="service_create" class="form-label">Услуга</label>
                                <select id="service_create" class="form-control select2" name="service_id" required>
                                    <option disabled selected>Выберите услугу</option>
                                    @foreach($patientDetails['services'] as $service)
                                        <option value="{{$service->id}}">{{ $service->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <input type="hidden" name="status" value="assigned">

                            <!-- Service Date -->
                            <div class="col-sm-6">
                                <label class="form-label" for="service_date">Дата услуги</label>
                                <input type="text" name="service_date" id="service_date"
                                       class="form-control flatpickr-date @error('service_date') is-invalid @enderror"
                                       value="{{ old('service_date') }}">
                                @error('service_date')
                                <div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Закрыть</button>
                        <button type="submit" class="btn btn-primary">Создать</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Assign Document --}}
    <div class="modal fade" id="createDocumentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Назначение Мед. Документ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('response.survey.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="patient_id" value="{{ $patientDetails['patient_id'] }}">
                            <input type="hidden" name="patient_service_id" id="modal_service_id">

                            <div class="row mb-3 mt-3">
                                <div class="col-12">
                                    <label class="form-label" for="survey-select">Мед. документы </label>
                                    <select id="survey-select" name="survey_id"
                                            class="form-control select2 @error('survey_id') is-invalid @enderror">
                                        <option value="" disabled selected>Выберите документ</option>
                                        @foreach($patientDetails['surveys'] as $survey)
                                            <option value="{{ $survey->id }}"
                                                    @selected($survey->id == old('survey_id')) style="background-color: {{ $survey->color }} !important;">{{ $survey->title }}</option>
                                        @endforeach
                                    </select>
                                    @error('survey_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div id="questions-container" class="mt-3"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Закрыть</button>
                        <button type="submit" class="btn btn-primary">Создать</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Модальное окно -->
    <div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="responseModalLabel">Ответы документа</h5>
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

    {{-- универсальное модальное окно  --}}
    <div class="modal fade" id="eventDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="eventModalContent">
                <div class="modal-header">
                    <h5 class="modal-title">Детали события</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Загрузка...
                </div>
            </div>
        </div>
    </div>

    {{-- Assign Serice --}}
    <div class="modal fade" id="diagnosisesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Создать диагноз</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('patient.diagnoses.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="patient_id" name="patient_id"
                               value="{{ $patientDetails['patient_id'] }}">

                        {{-- Избранные диагнозы --}}
                        @if($favoriteDiagnoses->count())
                            <div class="mb-3">
                                <h6 class="text-warning fw-bold">⭐ Избранные диагнозы</h6>
                                <div class="list-group">
                                    @foreach($favoriteDiagnoses as $diagnosis)
                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="#"
                                               class="list-group-item list-group-item-action diagnosis-item"
                                               data-diagnosis-id="{{ $diagnosis->id }}">
                                                {{ $diagnosis->name }}
                                            </a>
                                            <span class="ms-2 diagnosis-favorite text-warning cursor-pointer"
                                                  data-diagnosis-id="{{ $diagnosis->id }}"
                                                  title="Убрать из избранного"
                                                  style="font-size: 28px;">★</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Все остальные диагнозы --}}
                        <div>
                            <h6 class="text-secondary fw-bold">Все диагнозы</h6>
                            <div class="list-group">
                                @foreach($regularDiagnoses as $diagnosis)
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="#"
                                           class="list-group-item list-group-item-action diagnosis-item"
                                           data-diagnosis-id="{{ $diagnosis->id }}">
                                            {{ $diagnosis->name }}
                                        </a>
                                        <span class="ms-2 diagnosis-favorite text-muted cursor-pointer"
                                              data-diagnosis-id="{{ $diagnosis->id }}"
                                              title="Добавить в избранное"
                                              style="font-size: 28px;">★</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Закрыть</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="showDiagnosisesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    @foreach($patientDetails['diagnosises'] as $data)
                        <div class="border-bottom pb-4 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">
                                    <i class="bi bi-clipboard-data me-2 text-info"></i>
                                    {{ $data->date }}
                                </h5>
                                <div>
                            <span class="text-muted">
                                {{ \Carbon\Carbon::parse($data->created_at)->format('d.m.Y') }}
                            </span>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">
                                        <i class="bx bx-time-five text-info me-1"></i>
                                        Время</small>
                                    <div class="fs-5">
                                        {{ \Carbon\Carbon::parse($data->created_at)->format('Y-m-d H:i') }}
                                    </div>
                                </div>

                                <!-- Комментарий -->
                                @if($data->diagnosis->name)
                                    <div class="col-12 mt-3">
                                        <div class="medical-comment-block">
                                            <div class="comment-header">
                                                <h6 class="mb-0"><i class="bx bx-comment-detail me-1"></i>Диагноз</h6>
                                            </div>
                                            <div class="fs-5">
                                                {{ $data->diagnosis->name }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>


    {{-- indicativeModal --}}
    <div class="modal fade" id="indicativeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Результаты медицинских анализов</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <style>
                        table {
                            border-collapse: collapse;
                            width: 100%;
                            margin: 20px 0;
                            font-family: Arial, sans-serif;
                            font-size: 14px;
                        }

                        th, td {
                            border: 1px solid #ddd;
                            padding: 8px;
                            text-align: left;
                        }

                        th {
                            background-color: #f2f2f2;
                            font-weight: bold;
                        }

                        .analysis-header {
                            background-color: #e6f7ff;
                            font-weight: bold;
                        }

                        .parameter-name {
                            font-weight: bold;
                            background-color: #f9f9f9;
                        }
                    </style>

                    <table>
                        <!-- Биохимический анализ крови -->
                        <tr class="analysis-header">
                            <td colspan="6">Биохимический анализ крови</td>
                        </tr>
                        <tr>
                            <th>Показатель</th>
                            <th>15.01.2023</th>
                            <th>20.03.2023</th>
                            <th>10.05.2023</th>
                            <th>25.08.2023</th>
                            <th>12.11.2023</th>
                        </tr>
                        <tr>
                            <td class="parameter-name">Глюкоза (ммоль/л)</td>
                            <td>5.2 (ммоль/л)</td>
                            <td>5.4</td>
                            <td>5.1</td>
                            <td>5.7</td>
                            <td>5.3 норма (20)</td>
                        </tr>
                        <tr>
                            <td class="parameter-name">АЛТ (Ед/л)</td>
                            <td>22 (ммоль/л)</td>
                            <td>25</td>
                            <td>28</td>
                            <td>20</td>
                            <td>23 норма (20)</td>
                        </tr>
                        <tr>
                            <td class="parameter-name">АСТ (Ед/л)</td>
                            <td>18 (ммоль/л)</td>
                            <td>20</td>
                            <td>19</td>
                            <td>17</td>
                            <td>21 норма (20)</td>
                        </tr>
                        <tr>
                            <td class="parameter-name">Креатинин (мкмоль/л)</td>
                            <td>78 (ммоль/л)</td>
                            <td>82</td>
                            <td>80</td>
                            <td>85</td>
                            <td>79 норма (20)</td>
                        </tr>
                        <tr>
                            <td class="parameter-name">Мочевина (ммоль/л)</td>
                            <td>5.5 (ммоль/л)</td>
                            <td>5.8</td>
                            <td>5.6</td>
                            <td>6.0</td>
                            <td>5.7 норма (20)</td>
                        </tr>
                        <tr>
                            <td class="parameter-name">Билирубин общий (мкмоль/л)</td>
                            <td>12.5 (ммоль/л)</td>
                            <td>13.2</td>
                            <td>11.8</td>
                            <td>14.0</td>
                            <td>12.7 норма (20)</td>
                        </tr>

                        <!-- Общий анализ крови -->
                        <tr class="analysis-header">
                            <td colspan="6">Общий анализ крови</td>
                        </tr>
                        <tr>
                            <th>Показатель</th>
                            <th>10.02.2023</th>
                            <th>05.04.2023</th>
                            <th>20.06.2023</th>
                            <th>15.09.2023</th>
                            <th>30.12.2023</th>
                        </tr>
                        <tr>
                            <td class="parameter-name">Гемоглобин (г/л)</td>
                            <td>135</td>
                            <td>138</td>
                            <td>132</td>
                            <td>140</td>
                            <td>136</td>
                        </tr>
                        <tr>
                            <td class="parameter-name">Лейкоциты (×10⁹/л)</td>
                            <td>6.2</td>
                            <td>6.5</td>
                            <td>6.8</td>
                            <td>6.0</td>
                            <td>6.3</td>
                        </tr>
                        <tr>
                            <td class="parameter-name">Эритроциты (×10¹²/л)</td>
                            <td>4.5</td>
                            <td>4.6</td>
                            <td>4.4</td>
                            <td>4.7</td>
                            <td>4.5</td>
                        </tr>
                        <tr>
                            <td class="parameter-name">Тромбоциты (×10⁹/л)</td>
                            <td>220</td>
                            <td>230</td>
                            <td>210</td>
                            <td>240</td>
                            <td>225</td>
                        </tr>
                        <tr>
                            <td class="parameter-name">СОЭ (мм/ч)</td>
                            <td>8</td>
                            <td>10</td>
                            <td>9</td>
                            <td>7</td>
                            <td>8</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="instrumentalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Инструментальные исследования</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <style>
                        table {
                            border-collapse: collapse;
                            width: 100%;
                            margin: 20px 0;
                            font-family: Arial, sans-serif;
                            font-size: 14px;
                        }

                        th, td {
                            border: 1px solid #ddd;
                            padding: 8px;
                            text-align: left;
                        }

                        th {
                            background-color: #f2f2f2;
                            font-weight: bold;
                        }

                        .analysis-header {
                            background-color: #e6f7ff;
                            font-weight: bold;
                        }

                        .parameter-name {
                            font-weight: bold;
                            background-color: #f9f9f9;
                        }
                    </style>

                    <table>
                        <!-- УЗИ органов брюшной полости -->
                        <tr class="analysis-header">
                            <td colspan="6">УЗИ органов брюшной полости</td>
                        </tr>
                        <tr>
                            <th>Исследование</th>
                            <th>01.02.2023</th>
                            <th>10.04.2023</th>
                            <th>15.07.2023</th>
                            <th>20.10.2023</th>
                            <th>05.12.2023</th>
                        </tr>
                        <tr>
                            <td class="parameter-name">Печень</td>
                            <td>Не увеличена</td>
                            <td>Не изменена</td>
                            <td>Умеренная жировая инфильтрация</td>
                            <td>Не увеличена</td>
                            <td>В норме</td>
                        </tr>
                        <tr>
                            <td class="parameter-name">Желчный пузырь</td>
                            <td>Без изменений</td>
                            <td>Перегиб шейки</td>
                            <td>Холестериноз</td>
                            <td>Без конкрементов</td>
                            <td>В норме</td>
                        </tr>
                        <tr>
                            <td class="parameter-name">Поджелудочная железа</td>
                            <td>Четкие контуры</td>
                            <td>Слегка увеличена</td>
                            <td>Нормальная эхогенность</td>
                            <td>Без патологии</td>
                            <td>Без изменений</td>
                        </tr>
                        <tr>
                            <td class="parameter-name">Селезёнка</td>
                            <td>Размер 110 мм</td>
                            <td>Размер 112 мм</td>
                            <td>Размер 108 мм</td>
                            <td>Размер 113 мм</td>
                            <td>Размер 109 мм</td>
                        </tr>

                        <!-- ЭКГ -->
                        <tr class="analysis-header">
                            <td colspan="6">ЭКГ (электрокардиограмма)</td>
                        </tr>
                        <tr>
                            <th>Параметр</th>
                            <th>05.01.2023</th>
                            <th>15.03.2023</th>
                            <th>10.06.2023</th>
                            <th>25.09.2023</th>
                            <th>01.12.2023</th>
                        </tr>
                        <tr>
                            <td class="parameter-name">Ритм</td>
                            <td>Синусовый</td>
                            <td>Синусовый</td>
                            <td>Синусовая тахикардия</td>
                            <td>Синусовый</td>
                            <td>Синусовая брадикардия</td>
                        </tr>
                        <tr>
                            <td class="parameter-name">ЧСС (уд/мин)</td>
                            <td>78</td>
                            <td>80</td>
                            <td>98</td>
                            <td>76</td>
                            <td>62</td>
                        </tr>
                        <tr>
                            <td class="parameter-name">QT интервал</td>
                            <td>0.38 с</td>
                            <td>0.36 с</td>
                            <td>0.40 с</td>
                            <td>0.39 с</td>
                            <td>0.37 с</td>
                        </tr>
                        <tr>
                            <td class="parameter-name">Отклонение электрической оси</td>
                            <td>Норма</td>
                            <td>Норма</td>
                            <td>Умеренное отклонение влево</td>
                            <td>Норма</td>
                            <td>Норма</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="medicationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Добавить медикамент</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('patient.medication.store') }}" method="post">
                        @csrf
                        <input type="hidden" name="patient_id" value="{{ $patientDetails['patient_id'] }}">

                        <div class="mb_3">
                            <div id="medications-container"></div>

                            <div class="text-end">
                                <button type="button" class="btn btn-primary" id="btn-add-medication">
                                    <i class="fas fa-plus me-2"></i> Добавить медикамент
                                </button>
                            </div>

                            <template id="medication-template">
                                <div class="card mb-4 medication-row shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="card-title mb-0 medication-title">медикамент #1</h6>
                                            <button type="button" class="btn btn-sm btn-danger btn-remove-medication">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>

                                        <!-- Основной медикамент -->
                                        <div class="mb-3">
                                            <label class="form-label">Основной медикамент</label>
                                            <select name="medications[]" class="form-control medication-select"
                                                    required>
                                                @foreach($medications as $medication)
                                                    <option value="{{ $medication->id }}"
                                                            data-active-substance="{{ $medication->composition->active_substance_name }}">
                                                        {{ $medication->name }}
                                                        ({{ $medication->composition->active_substance_name }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="d-flex flex-row gap-2 medication_information mt-2"></div>
                                        </div>

                                        <!-- Дозировка -->
                                        <div class="mb-3">
                                            <label class="form-label">Дозировка</label>
                                            <input type="text" name="dosages[]" class="form-control"
                                                   value=""
                                                   placeholder="Дозировка" required>
                                        </div>

                                        <!-- Период приёма -->
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Период приёма</label>
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
                                                       required
                                                       class="admission-hidden-input">
                                            </div>
                                        </div>

                                        <!-- Совместимые медикаменты -->
                                        <div class="mb-2 compatible-medications-wrapper">
                                            <label class="form-label">Совместимые медикаменты</label>
                                            <div class="compatible-list"></div>

                                            <div class="row g-2 mt-2">
                                                <div class="col-md-6">
                                                    <select class="form-control compatible-medication-select">
                                                        <option value="" disabled selected>Выберите медикамент</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control compatible-dosage"
                                                           placeholder="Дозировка">
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button"
                                                            class="btn btn-outline-primary w-100 btn-add-compatible">
                                                        Добавить
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <button type="submit" class="btn btn-outline-primary w-100 mt-3">
                            Сохранить
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- show view modal  --}}
    <script>
        $(document).on('click', '.history-header', function () {
            const id = $(this).data('id');
            const type = $(this).data('type');

            $.ajax({
                url: '{{ route('admin.patients.history.partial') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    type: type,
                    id: id
                },
                success: function (html) {
                    console.log(html)
                    $('#eventDetailModal .modal-body').html(html);
                    $('#eventDetailModal').modal('show');
                },
                error: function () {
                    $('#eventDetailModal .modal-body').html('<div class="text-danger">Ошибка загрузки данных</div>');
                    $('#eventDetailModal').modal('show');
                }
            });
        });
    </script>

    {{--  Modals  --}}
    <script>
        $(document).ready(function () {
            $('button[data-type-id][data-bs-target="#createServiceModal"]').on('click', function (e) {
                e.preventDefault();
                var button = $(this);
                var typeId = button.data('typeId');
                var modal = $(button.data('bs-target'));

                $.ajax({
                    url: '/api/service/by-type/' + typeId,
                    method: 'GET',
                    cache: false,
                    success: function (data) {
                        var select = $('#service_create');
                        select.empty().append('<option disabled selected>Выберите услугу</option>');
                        $.each(data, function (index, service) {
                            select.append(new Option(service.name, service.id));
                        });
                        modal.modal('show');
                    },
                    error: function () {
                        console.error('Ошибка при загрузке услуг');
                    }
                });
            });

            $('#createServiceModal').on('shown.bs.modal', function () {
                $('#service_create').select2({
                    dropdownParent: $(this),
                    width: '100%',
                    placeholder: "Выберите услугу",
                });
                $('#place').select2({
                    dropdownParent: $(this),
                    width: '100%',
                });
            });

            $('#createServiceModal').on('hidden.bs.modal', function () {
                $('#service_create').select2('destroy');
                $('#place').select2('destroy');
                $('#service_create').empty().append('<option disabled selected>Выберите услугу</option>');
            });

            $('#createMapModal').on('shown.bs.modal', function () {
                $('#service').select2({
                    dropdownParent: $('#createMapModal'),
                    width: '100%',
                    placeholder: "Выберите услугу",
                });
            });

            $('#createMapModal').on('hidden.bs.modal', function () {
                $('#service').select2('destroy');
            });

            $('#createDocumentModal').on('shown.bs.modal', function (event) {
                $('#survey-select').select2({
                    dropdownParent: $('#createDocumentModal'),
                    width: '100%',
                    placeholder: "Выберите услугу",
                });

                const button = $(event.relatedTarget);
                const serviceId = button.data('service-id') || null;

                $('#modal_service_id').val(serviceId);
            });

            $('#createDocumentModal').on('hidden.bs.modal', function () {
                $('#survey-select').select2('destroy');
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            // Мед. документы
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
                console.log("OPTIONS RAW:", q.options);

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

            $('.service_status').on('change', function () {
                const serviceId = $(this).data('service-id');
                const newStatus = $(this).val();

                $.ajax({
                    url: `/api/patient/service/update-status/${serviceId}`,
                    method: 'GET',
                    data: {
                        status: newStatus
                    },
                    beforeSend: function () {
                        // Блокировка элемента на время запроса
                        $('#service_status').prop('disabled', true);
                    },
                    success: function (response) {
                        Toast.fire({
                            icon: 'success',
                            title: 'Статус успешно обновлен'
                        });
                    },
                    error: function (xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Ошибка: ' + xhr.responseJSON.message
                        });
                        // Возвращаем предыдущее значение
                        $('#service_status').val(triggeredStatus).trigger('change');
                    },
                    complete: function () {
                        $('#service_status').prop('disabled', false);
                    }
                });
            });
        });

        function toggleHistoryItem(header) {
            const content = header.nextElementSibling;
            const icon = header.querySelector('.collapsible-icon');

            content.classList.toggle('show');
            icon.classList.toggle('rotated');
        }
    </script>

    <script>
        $(function () {
            $('#select-document-form').on('submit', function (e) {
                e.preventDefault();

                let documentId = $('#document_id').val();
                let patientId = $('#patient_id').val();

                if (!documentId) {
                    alert('Пожалуйста, выберите документ');
                    return;
                }
                if (!patientId) {
                    alert('Не найден patient_id');
                    return;
                }

                let url = '/dashboard/documents/'
                    + encodeURIComponent(documentId)
                    + '?patient_id='
                    + encodeURIComponent(patientId);

                window.location.href = url;
            });
        });
    </script>

    <script>
        // medications
        const containerMedicaments = document.getElementById('medications-container');
        const templateMedicaments = document.getElementById('medication-template');

        // Удалена инициализация Select2

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
            $select.val(''); // Сбрасываем значение
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

        function bindMedicationChange(select) {
            $(select).on('change', function (e) {
                const selectedOption = this.options[this.selectedIndex];
                const selectedId = selectedOption.value;
                const activeSubstance = $(selectedOption).data('active-substance');
                const $row = $(this).closest('.medication-row');
                const $compatibleSelect = $row.find('.compatible-medication-select');

                // Загрузка совместимых медикаментов
                fetch(`/api/medication/active-substance/${activeSubstance}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Ошибка загрузки');
                        return response.json();
                    })
                    .then(data => {
                        // Сохраняем текущее значение
                        const currentValue = $compatibleSelect.val();

                        // Очищаем и добавляем новые опции
                        $compatibleSelect.empty();
                        $compatibleSelect.append(
                            '<option value="" disabled selected>Выберите медикамент</option>'
                        );

                        data.forEach(item => {
                            const option = new Option(
                                `${item.name} (${item.composition.active_substance_name})`,
                                item.id
                            );
                            $compatibleSelect.append(option);
                        });

                        // Восстанавливаем значение
                        if (currentValue) {
                            $compatibleSelect.val(currentValue);
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка:', error);
                        $compatibleSelect.empty().append(
                            '<option value="" disabled selected>Ошибка загрузки</option>'
                        );
                    });
            });
        }

        // Добавление новой строки
        let medicationCount = 0;
        $('#btn-add-medication').click(function () {
            const clone = templateMedicaments.content.cloneNode(true);
            containerMedicaments.appendChild(clone);
            medicationCount++;

            const $lastRow = $(containerMedicaments).find('.medication-row').last();
            $lastRow.find('.medication-title').text(`медикамент #${medicationCount}`);

            // Инициализация основного медикамента
            const $medicationSelect = $lastRow.find('.medication-select');
            bindMedicationChange($medicationSelect[0]);

            // Инициализация события change для совместимых медикаментов
            $medicationSelect.trigger('change');
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

        // Инициализация существующих элементов
        $(document).ready(function () {
            $('.medication-select').each(function () {
                bindMedicationChange(this);
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            // Добавление/удаление диагноза пациенту
            $('.diagnosis-item').on('click', function (e) {
                e.preventDefault();

                let diagnosisId = $(this).data('diagnosis-id');
                let patientId = $('#patient_id').val();

                $.ajax({
                    url: "{{ route('patient.diagnoses.store') }}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    contentType: "application/json",
                    dataType: "json",
                    data: JSON.stringify({
                        patient_id: patientId,
                        diagnosis_id: diagnosisId
                    }),
                    success: function (response) {
                        alert("Диагноз успешно добавлен");
                        location.reload();
                    },
                    error: function (xhr) {
                        alert("Ошибка при добавлении диагноза.");
                        console.error(xhr.responseText);
                    }
                });
            });

            // Добавление/удаление из избранного
            $('.diagnosis-favorite').on('click', function (e) {
                e.stopPropagation(); // Не даём сработать родительскому click

                const star = $(this);
                const diagnosisId = star.data('diagnosis-id');

                $.ajax({
                    url: '/api/selected-diagnosis', // твой API
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        diagnosis_id: diagnosisId,
                        admin_id: {{ auth()->user()->id }}
                    },
                    success: function () {
                        location.reload(); // Можно заменить на DOM-обновление без reload
                    },
                    error: function () {
                        alert("Ошибка при обновлении избранных.");
                    }
                });
            });
        });
    </script>
@endsection
