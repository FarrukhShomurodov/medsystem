@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Карта пациента</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">/</span> {{ $mapDetails['patient']['full_name'] }}
    </h6>

    <div class="card">
        <div class="card-header">
            <h5>Основная информация</h5>
        </div>
        <div class="card-body">
            <p><strong>ID карты:</strong> {{ $mapDetails['id'] }}</p>
            <p><strong>Пациент:</strong> <a
                    href="{{ route('patients.show', $mapDetails['patient']['id']) }}"> {{ $mapDetails['patient']['full_name'] }}
                    (ID: {{ $mapDetails['patient']['id'] }}) </a></p>
            <p><strong>Дата создания:</strong> {{ $mapDetails['created_at'] }}</p>
            <p><strong>Лечащий врач:</strong> {{ $mapDetails['doctor']['name'] }}
                ({{ $mapDetails['doctor']['speciality'] }})</p>
            <p><strong>Учреждение:</strong> {{ $mapDetails['institution']['name'] }}</p>

            <p><strong>Форма лечения:</strong> {{ $mapDetails['is_outpatient'] ? 'Амбулаторно' : 'Стационарно'}}</p>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5>Госпитализация</h5>
        </div>
        <div class="card-body">
            <p><strong>Дата госпитализации:</strong> {{ $mapDetails['admission']['date'] ?? 'Не указано' }}</p>
            <p><strong>Дата выписки:</strong> {{ $mapDetails['admission']['discharge_date'] ?? 'Не указано' }}</p>
            <p><strong>Диагноз при
                    госпитализации:</strong> {{ $mapDetails['admission']['admission_diagnosis']['code'] }}
                - {{ $mapDetails['admission']['admission_diagnosis']['description'] }}</p>
            <p><strong>Диагноз при выписке:</strong> {{ $mapDetails['admission']['discharge_diagnosis']['code'] }}
                - {{ $mapDetails['admission']['discharge_diagnosis']['description'] }}</p>
            <p><strong>Цель обследования:</strong> {{ $mapDetails['admission']['purpose'] }}</p>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5>Анамнез</h5>
        </div>
        <div class="card-body">
            <p><strong>Жалобы:</strong> {{ $mapDetails['anamnesis']['complaints'] }}</p>
            <p><strong>История заболевания:</strong> {{ $mapDetails['anamnesis']['disease_history'] }}</p>
            <p><strong>Общий анамнез:</strong> {{ $mapDetails['anamnesis']['general_anamnesises'] }}</p>
        </div>
    </div>


    <div class="card mt-3">
        <div class="card-datatable table-responsive col-12">
            <div class="card-header">
                <h5>Обследования</h5>
            </div>
            @if($mapDetails['examinations']['analyzes']->isNotEmpty())
                @foreach($mapDetails['examinations']['analyzes'] as $analyze)
                    @if(!empty($analyze['results']))
                        <table class="datatables-users table border-top">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Тип анализа</th>
                                <th>Единица измерения</th>
                                <th>Значение</th>
                                <th>Референсный диапазон</th>
                                <th>Комментарий</th>
                                <th>Дата</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($analyze['results'] as $result)
                                <tr>
                                    <td>{{ $result['id'] }}</td>
                                    <td>{{ $result['type']->name }}</td>
                                    <td>{{ $result['unit'] }}</td>
                                    <td>{{ $result['value'] }}</td>
                                    <td>{{ $result['reference_range'] }}</td>
                                    <td>{{ $result['comment'] }}</td>
                                    <td>{{ $analyze['date'] }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="card-body">
                            <p>Нет данных</p>
                        </div>
                    @endif
                @endforeach
            @else
                <div class="card-body">
                    <p>Данные обследований отсутствуют.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-datatable table-responsive col-12">
            <div class="card-header">
                <h5>Услуги</h5>
            </div>
            @if($mapDetails['examinations']['services']->isNotEmpty())
                <table class="datatables-users table border-top">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Услуга</th>
                        <th>Дата услуги</th>
                        <th>Время услуги</th>
                        <th>Место оказания</th>
                        <th>Результаты</th>
                        <th>Статус</th>
                        <th>Статус оплаты</th>
                        <th>Страховая компания</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($mapDetails['examinations']['services'] as $service)
                        <tr>
                            <td>{{ $service->id }}</td>
                            <td>{{ $service->service->name }}</td>
                            <td>{{ $service->service_date }}</td>
                            <td>{{ $service->service_time }}</td>
                            <td>{{ $service->place }}</td>
                            <td>{{ $service->result }}</td>
                            <td> {!! $service->getStatusBadge() !!} </td>
                            <td>{{ $service->payment }}</td>
                            <td>{{ $service->insurance_policy }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <div class="card-body">
                    <p>Данные услуги отсутствуют.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-datatable table-responsive col-12">
            <div class="card-header">
                <h5>Услуги</h5>
            </div>
            @if($mapDetails['examinations']['services']->isNotEmpty())
                <table class="datatables-users table border-top">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Услуга</th>
                        <th>Дата услуги</th>
                        <th>Время услуги</th>
                        <th>Место оказания</th>
                        <th>Результаты</th>
                        <th>Статус</th>
                        <th>Статус оплаты</th>
                        <th>Страховая компания</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($mapDetails['examinations']['services'] as $service)
                        <tr>
                            <td>{{ $service->id }}</td>
                            <td>{{ $service->service->name }}</td>
                            <td>{{ $service->service_date }}</td>
                            <td>{{ $service->service_time }}</td>
                            <td>{{ $service->place }}</td>
                            <td>{{ $service->result }}</td>
                            <td> {!! $service->getStatusBadge() !!} </td>
                            <td>{{ $service->payment }}</td>
                            <td>{{ $service->insurance_policy }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <div class="card-body">
                    <p>Данные услуги отсутствуют.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-datatable table-responsive col-12">
            <div class="card-header">
                <h5>Мед. документы</h5>
            </div>
            @if($mapDetails['examinations']['surveyResponses']->isNotEmpty())
                <table class="datatables-users table border-top">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Документ</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($mapDetails['examinations']['surveyResponses']->sortByDesc('id')->take(5) as $response)
                        <tr style="background-color: {{ $response->survey->color }}">
                            <td>{{$response->id}}</td>
                            <td>
                                <p class="text-primary mb-0">{{ $response->survey->title }}</p>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info view-response"
                                        data-id="{{ $response->id }}"
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
            @else
                <div class="card-body">
                    <p>Данные документа отсутствуют.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
@section('scripts')
    <script>
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
                            <label class="form-check-label">
                                <strong>${index + 1}. ${answer.question.question}</strong>
                                <br>Ответ: <span class="text-success">${localizedAnswer}</span>
                            </label>
                        </div>
                    `;
                        });
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
    </script>
@endsection
