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

    <div class="card">
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


    {{--                <button--}}
    {{--                    type="button"--}}
    {{--                    class="btn btn-primary me-3"--}}
    {{--                    data-bs-toggle="modal"--}}
    {{--                    data-bs-target="#createMapModal">--}}
    {{--                    Препараты--}}
    {{--                </button>--}}

    {{--                <button--}}
    {{--                    type="button"--}}
    {{--                    class="btn btn-primary me-3"--}}
    {{--                    data-bs-toggle="modal"--}}
    {{--                    data-bs-target="#createMapModal">--}}
    {{--                    Процедуры--}}
    {{--                </button>--}}

    <div class="card mt-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">История посещений клиники</h5>
            <div>
                <button
                    type="button"
                    class="btn btn-primary me-3"
                    data-bs-toggle="modal"
                    data-bs-target="#createServiceModal">
                    Новая услуга
                </button>

                <button
                    type="button"
                    class="btn btn-primary me-3"
                    data-bs-toggle="modal"
                    data-bs-target="#createDocumentModal">
                    Мед Документ
                </button>

                <button
                    type="button"
                    class="btn btn-primary me-3"
                    data-bs-toggle="modal"
                    data-bs-target="#createMapModal">
                    Направление
                </button>

                <button
                    onclick="window.location.href='{{ route('patient.map.create', $patientDetails['patient_id']) }}'"
                    class="btn btn-primary me-3"
                    @disabled(!$patientDetails['history']['map_is_active'])>Создать госпитализацию
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="history-section">
                @forelse($patientDetails['timeline'] as $event)
                    <div class="history-item">
                        <div class="history-header" onclick="toggleHistoryItem(this)">
                            <div>
                            <span class="history-title">
                                @switch($event['type'])
                                    @case('examination_map') Госпитализация @break
                                    @case('assignment') Направление @break
                                    @case('service') Оказанная услуга @break
                                    @case('patient_med_document') Мед. Документ @break
                                    @case('reception') Приемы @break
                                @endswitch
                            </span>
                                <span class="history-date">
                                    @if($event['date'] instanceof \Carbon\Carbon)
                                        {{ $event['date']->format('d.m.Y') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($event['date'])->format('d.m.Y') }}
                                    @endif

                                    @if($event['type'] === 'examination_map' && $event['end_date'])
                                        -
                                        @if($event['end_date'] instanceof \Carbon\Carbon)
                                            {{ $event['end_date']->format('d.m.Y') }}
                                        @else
                                            {{ \Carbon\Carbon::parse($event['end_date'])->format('d.m.Y') }}
                                        @endif
                                    @endif
                                </span>
                            </div>
                            <span class="collapsible-icon">▼</span>
                        </div>
                        <div class="history-content">
                            @include('admin.patients.history.'.$event['type'], ['data' => $event['data']])
                        </div>
                    </div>
                @empty
                    <div class="text-center py-3">Нет данных о посещениях</div>
                @endforelse
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
@endsection

@section('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script>
        $(document).ready(function () {
            // Modals
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

            $('#createServiceModal').on('shown.bs.modal', function () {
                $('#service_create').select2({
                    dropdownParent: $('#createServiceModal'),
                    width: '100%',
                    placeholder: "Выберите услугу",
                });

                $('#place').select2({
                    dropdownParent: $('#createServiceModal'),
                    width: '100%',
                });
            });

            $('#createServiceModal').on('hidden.bs.modal', function () {
                $('#service_create').select2('destroy');
                $('#place').select2('destroy');
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

            $('.service_status').on('change', function() {
                const serviceId = $(this).data('service-id');
                const newStatus = $(this).val();

                $.ajax({
                    url: `/api/patient/service/update-status/${serviceId}`,
                    method: 'GET',
                    data: {
                        status: newStatus
                    },
                    beforeSend: function() {
                        // Блокировка элемента на время запроса
                        $('#service_status').prop('disabled', true);
                    },
                    success: function(response) {
                        Toast.fire({
                            icon: 'success',
                            title: 'Статус успешно обновлен'
                        });
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Ошибка: ' + xhr.responseJSON.message
                        });
                        // Возвращаем предыдущее значение
                        $('#service_status').val(triggeredStatus).trigger('change');
                    },
                    complete: function() {
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
@endsection
