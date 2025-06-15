@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Пациент</title>
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
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">История посещений клиники</h5>
            <div>
                <button
                    type="button"
                    class="btn btn-primary me-3"
                    data-bs-toggle="modal"
                    data-type-id="4"
                    data-bs-target="#createServiceModal">
                    Инструментальные исследования
                </button>

                <button
                    type="button"
                    class="btn btn-primary me-3"
                    data-bs-toggle="modal"
                    data-type-id="5"
                    data-bs-target="#createServiceModal">
                    Лаборатория
                </button>

                <a
                    type="button"
                    class="btn btn-primary me-3"
                    target="_blank"
                    href="{{ route('receptions.appointment') }}">
                    Консультация
                </a>

                <button
                    type="button"
                    class="btn btn-primary me-3"
                    data-bs-toggle="modal"
                    data-bs-target="#diagnosisesModal">
                    Добавить диагноз
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
                        <label class="form-label" for="full_name">ФИО</label>
                        <div class="input-group input-group-merge">
                            <input type="text" name="full_name" value="{{ request("full_name") }}"
                                   id="full_name"
                                   class="form-control"
                                   placeholder="ФИО"/>
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

                    <div class="col-md-2">
                        <label class="form-label" for="basic-icon-birth-date"></label>
                        <div class="input-group input-group-merge">
                            <button type="submit" class="btn btn-primary">Поиск</button>
                        </div>
                    </div>
                </div>
            </form>

            <a class="btn btn-sm btn-primary" href="{{ url()->previous() }}">
                Вернуться в отделение
            </a>
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
                                    Услуга
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
                                @case('diagnosis')
                                    {{ \Illuminate\Support\Str::limit($event['data']->description, 10, '...') }}
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
                                                    {{ \Illuminate\Support\Str::limit($event['data']->description, 10, '...')}}
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

    <div class="card mt-3">
        <div class="card-header">
            <h5>Контактная информация</h5>
        </div>
        <div class="card-body">
            <p><strong>Телефон:</strong> {{ $patientDetails['contact_info']['phone'] }}</p>
            <p><strong>Доп. телефон:</strong> {{ $patientDetails['contact_info']['additional_phone'] }}</p>
            <p><strong>Email:</strong> {{ $patientDetails['contact_info']['email'] }}</p>
            <p><strong>Адрес проживания:</strong> {{ $patientDetails['contact_info']['residential_address'] }}</p>
            <p><strong>Адрес регистрации:</strong> {{ $patientDetails['contact_info']['registered_address'] }}</p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Страховая информация</h5>
        </div>
        <div class="card-body">
            <p><strong>Номер полиса:</strong> {{ $patientDetails['insurance_info']['policy_number'] }}</p>
            <p><strong>Компания:</strong> {{ $patientDetails['insurance_info']['company'] }}</p>
            <p><strong>Тип:</strong> {{ $patientDetails['insurance_info']['type'] }}</p>
            <p><strong>Срок действия:</strong> {{ $patientDetails['insurance_info']['expiry_date'] }}</p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Экстренный контакт</h5>
        </div>
        <div class="card-body">
            <p><strong>ФИО:</strong> {{ $patientDetails['emergency_contact']['full_name'] }}</p>
            <p><strong>Отношение:</strong> {{ $patientDetails['emergency_contact']['relation'] }}</p>
            <p><strong>Телефон:</strong> {{ $patientDetails['emergency_contact']['phone'] }}</p>
            <p><strong>Адрес:</strong> {{ $patientDetails['emergency_contact']['address'] }}</p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Общая информация</h5>
        </div>
        <div class="card-body">
            <p><strong>ФИО:</strong> {{ $patientDetails['basic_info']['full_name'] }}</p>
            <p><strong>Дата рождения:</strong> {{ $patientDetails['basic_info']['birth_date'] }}</p>
            <p><strong>Пол:</strong> {{ $patientDetails['basic_info']['gender'] }}</p>
            <p><strong>Гражданство:</strong> {{ $patientDetails['basic_info']['citizenship'] }}</p>
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
                            <div class="col-12 mb-3">
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

                            <!-- Place -->
                            <div class="col-sm-6">
                                <label class="form-label" for="place">Место оказания </label>
                                <select id="place" name="place"
                                        class="form-control select2 @error('place') is-invalid @enderror">
                                    @foreach(\App\Models\PatientService::PLACE as $value)
                                        <option value="{{ $value }}" @selected(old('place') == $value)>
                                            {{ $value == 'institution' ? 'Учреждение' : 'Кабинет' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('place')
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
                <form action="{{ route('diagnosises.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="patient_id" value="{{ $patientDetails['patient_id'] }}">
                        <div class="row">
                            <div class="col-12">
                                <textarea id="basic-icon-default-history" class="form-control" placeholder="Диагноз"
                                          name="description"></textarea>
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
@endsection
