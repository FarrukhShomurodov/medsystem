@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Опросник</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">/</span>Опросники
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
            <h5 class="mb-0">Опросники (Пациент: {{ $map->patient->first_name }})</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('map.survey.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

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
                                <button type="button" class="btn btn-sm btn-info view-response" data-id="{{ $response->id }}"
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

                <input type="hidden" name="examination_map_id" value="{{ $map->id }}">

                <div class="row mb-3 mt-3">
                    <div class="col-12">
                        <label class="form-label" for="survey-select">Опросники <span
                                class="text-danger">*</span></label>
                        <select id="survey-select" name="survey_id"
                                class="form-control select2 @error('survey_id') is-invalid @enderror"
                                required>
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

                    <div id="service-container" class="mt-3">
                        <div class="row g-3">
                            {{-- status --}}
                            <input type="hidden" name="service_status" value="assigned">

                            <div class="col-12">
                                <label class="form-label" for="service_id">Услуга</label>
                                <select id="service_id" name="service_id"
                                        class="form-control select2 @error('service_id') is-invalid @enderror">
                                    <option value="" disabled selected>Выберите услугу</option>
                                    @foreach($services as $service)
                                        <option
                                            value="{{ $service->id }}" @selected(old('service_id') == $service->id)>
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
                                        <option
                                            value="{{ $staff->id }}" @selected(in_array($staff->id, old('staffs', [])))>
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
                                                    data-placeholder="Выберите или введите новую процедуру"
                                                    required>
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
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#survey-select').on('change', function () {
                let surveyId = $(this).val();
                let questionsContainer = $('#questions-container');

                questionsContainer.html('');

                if (!surveyId) return;

                $.ajax({
                    url: `/api/surveys/${surveyId}/questions`,
                    type: 'GET',
                    success: function (data) {
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

                $('#responseModal').modal('hide'); // Закрываем модальное окно
            });

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

        })
    </script>
@endsection
