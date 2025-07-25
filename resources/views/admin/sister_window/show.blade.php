@extends('admin.layouts.app')
<style>
    .tab {
        padding: 10px 15px;
        cursor: pointer;
        background-color: #f1f1f1;
        margin-right: 5px;
        border-radius: 5px;
    }
    .tab.active {
        background-color: #007bff;
        color: #fff;
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
    .status-indicator {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 5px;
    }
    .status-indicator.pending { background: orange; }
    .status-indicator.completed { background: green; }
    .status-indicator.not-completed { background: red; }
</style>

@section('content')


    <div class="telegram-alert alert alert-info">🩺 Новое: ЭКГ в 10:00</div>
    <div class="telegram-alert alert alert-success">🔬 Лаб. анализ: ОАК</div>
    <div class="telegram-alert alert alert-warning">💉 Инъекция: Цефтриаксон</div>

    <style>
        .telegram-alert {
            border-radius: 10px;
            padding: 10px 15px;
            font-size: 15px;
            margin-bottom: 8px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
    </style>

    <div class="card">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Пациенты </h5>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>ФИО / ID</th>
                    <th>№ Палаты</th>
                    <th>Возраст</th>
                    <th>Вес</th>
                    <th>Рост и ИМТ</th>
                    <th>Дней в стационаре</th>
                    <th>Аллергия</th>
                </tr>
                </thead>
                <tbody>
                @foreach($patients as $p)
                    <tr class="patient-row cursor-pointer @if($patient->id == $p->id) bg-warning text-dark @endif"
                        onclick="window.location.href='{{ route('sister.window.show',$p->id) }}'">
                        <td>{{ $p->first_name }} {{ $p->last_name }} {{ $p->middle_name }}
                            ID{{ $p->id }}</td>
                        <td>-</td>
                        <td>{{ \Carbon\Carbon::parse($p->birth_date)->age }} лет</td>
                        <td>-</td>
                        <td>-</td>
                        <td>{{ \Carbon\Carbon::parse($p->admission_date)->age }}</td>
                        <td>-</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>


    <div class="text-end">
        <button type="button" class="btn btn-primary mt-4" data-bs-toggle="modal"
                data-bs-target="#addParameterModal">
            Добавить данные
        </button>

        <button type="button" class="btn btn-primary mt-4" data-bs-toggle="modal"
                data-bs-target="#treatmentPlanModal">
            План лечения
        </button>
    </div>

    {{-- Контейнер со страницей --}}
    <div class="card mt-4">
        <div class="row card-body">
            {{-- Меню слева --}}
            <div class="col-md-3">
                <div class="list-group mb-4" id="parameter-menu"
                     style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                    @foreach($menu as $key => $item)
                        <button
                            class="list-group-item list-group-item-action d-flex align-items-center p-3 position-relative @if($loop->first) active @endif"
                            data-target="{{ $key }}"
                            style="
                                @if($item['graphic']) border-left: 4px solid #4CAF50; @endif
                                transition: all 0.3s ease;
                                border: none;
                                border-bottom: 1px solid #f0f0f0;
                            ">
                            <div class="d-flex align-items-center w-100">
                                <div class="parameter-icon me-3" style="
                                        width: 36px;
                                        height: 36px;
                                        background: #f5f7fa;
                                        border-radius: 10px;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        transition: all 0.3s ease;
                                    ">
                                    @switch($key)
                                        @case('general')
                                            <i class='bx bx-line-chart'
                                               style="color: #4CAF50; font-size: 1.2rem;"></i>
                                            @break
                                        @case('breathing_rate')
                                            <i class='bx bx-line-chart'
                                               style="color: #2196F3; font-size: 1.2rem;"></i>
                                            @break
                                        @case('consciousness')
                                            <i class='bx bx-brain'
                                               style="color: #9C27B0; font-size: 1.2rem;"></i>
                                            @break
                                        @case('oxygen_type')
                                            <i class='bx bx-wind'
                                               style="color: #FF9800; font-size: 1.2rem;"></i>
                                            @break
                                        @case('total_score')
                                            <i class='bx bx-star'
                                               style="color: #FF5722; font-size: 1.2rem;"></i>
                                            @break
                                    @endswitch
                                </div>

                                <span class="fw-medium">{{ $item['label'] }}</span>

                                <div class="ms-auto">
                                    <span class="badge bg-light text-dark rounded-pill" style="
                                        background: rgba(0,0,0,0.05) !important;
                                        font-weight: 500;
                                        font-size: 0.8rem;
                                    ">
                                        {{ $loop->iteration }}
                                    </span>
                                </div>
                            </div>

                            <div class="active-indicator position-absolute end-0 me-3" style="
                                    opacity: 0;
                                    transform: translateX(-10px);
                                    transition: all 0.3s ease;
                            ">
                                <i class="bi bi-chevron-right"></i>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Секции графиков --}}
            <div class="col-md-9">
                {{-- Общие данные (mixed chart) --}}
                <div id="general" class="chart-section mb-4">
                    <div id="generalChart"></div>
                </div>

                {{-- Одиночные --}}
                <div id="breathing_rate" class="chart-section d-none mb-4">
                    <div id="breathingChart"></div>
                </div>
                <div id="consciousness" class="chart-section d-none mb-4">
                    <div id="consciousnessChart"></div>
                </div>
                <div id="oxygen_type" class="chart-section d-none mb-4">
                    <div id="oxygenChart"></div>
                </div>
                <div id="total_score" class="chart-section d-none mb-4">
                    <div id="totalChart"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Журнал </h5>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>Дата/Время</th>
                    <th>Систолическое АД</th>
                    <th>Диастолическое АД</th>
                    <th>Пульс</th>
                    <th>Температура</th>
                    <th>Сатурация</th>
                    <th>ЧДД</th>
                    <th>Сознание</th>
                    <th>Кислород/воздух</th>
                    <th>Диурез</th>
                    <th>Общий балл</th>
                </tr>
                </thead>
                <tbody>
                @foreach($records as $r)
                    <tr>
                        <td>{{ $r->datetime }}</td>
                        <td>{{ $r->systolic }}</td>
                        <td>{{ $r->diastolic }}</td>
                        <td>{{ $r->pulse }}</td>
                        <td>{{ $r->temperature }}</td>
                        <td>{{ $r->saturation }}</td>
                        <td>{{ $r->breathing_rate }}</td>
                        <td>{{ $r->consciousness }}</td>
                        <td>{{ $r->oxygen_type }}</td>
                        <td>{{ $r->urine_balance }}</td>
                        <td>{{ $r->total_score }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Документы</h5>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th></th>
                    @foreach($examinationMap->documentResponse as $response)
                        <th>{{ \Carbon\Carbon::parse($response->created_at)->format('Y-m-d') }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach($documents as $document)
                    <tr>
                        <td>
                            <a href="{{ route('documents.show', ['document' => $document->id , 'patient_id' => $patient->id] ) }}">
                                <strong>{{ $document->title }}</strong>
                            </a>
                        </td>


                        @foreach($examinationMap->documentResponse as $response)
                            @if($response->document_id == $document->id)
                                <td class="cursor-pointer"
                                    data-bs-toggle="modal"
                                    data-bs-target="#recommendActionModal"
                                    data-document-id="{{ $response->document_id }}"
                                    data-response-id="{{ $response->id }}">
                                    {{ $response->total_score }}
                                </td>
                            @else
                                <td></td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="addParameterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Добавить данные пациента</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('patient.vital.parameters.store',$patient->id) }}"
                          method="post">
                        @csrf

                        <div class="row g-3">
                            @foreach($vitalParameters as $param)
                                <div class="col-md-6 flex-fill">
                                    <label for="param_{{ $param->id }}" class="form-label">{{ $param->name }}</label>
                                    <input
                                        type="number"
                                        step="0.1"
                                        name="parameters[{{ $param->id }}]"
                                        id="param_{{ $param->id }}"
                                        class="form-control"
                                        value="{{ old('parameters.' . $param->id) }}"
                                    >
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
    </div>

    <div class="modal fade" id="treatmentPlanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">План лечения пациента</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="tabs d-flex mb-3">
                            <div class="tab active" onclick="switchTab('medications')">Препараты</div>
                            <div class="tab" onclick="switchTab('procedures')">Манипуляции</div>
                            <div class="tab" onclick="switchTab('researches')">Исследования</div>
                        </div>

                        <!-- Препараты -->
                        <div id="medications" class="tab-content active">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>Время</th>
                                    <th>Препарат</th>
                                    <th>Дозировка</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr><td>07:00</td><td>Омепразол</td><td>20 мг</td><td><span class="status-indicator pending"></span>Ожидает</td><td><button class="btn btn-success btn-sm" onclick="markCompleted(this)">Принято</button><button class="btn btn-danger btn-sm" onclick="showReason(this)">Отказ</button><input type="text" class="form-control mt-1 reason-input" placeholder="Причина отказа..." style="display:none;"></td></tr>
                                <tr><td>08:00</td><td>Амоксициллин</td><td>500 мг</td><td><span class="status-indicator completed"></span>Принято</td><td><button class="btn btn-success btn-sm" onclick="markCompleted(this)">Принято</button><button class="btn btn-danger btn-sm" onclick="showReason(this)">Отказ</button><input type="text" class="form-control mt-1 reason-input" placeholder="Причина отказа..." style="display:none;"></td></tr>
                                <tr><td>10:00</td><td>Цефтриаксон</td><td>1 г</td><td><span class="status-indicator pending"></span>Ожидает</td><td><button class="btn btn-success btn-sm" onclick="markCompleted(this)">Принято</button><button class="btn btn-danger btn-sm" onclick="showReason(this)">Отказ</button><input type="text" class="form-control mt-1 reason-input" placeholder="Причина отказа..." style="display:none;"></td></tr>
                                <tr><td>12:00</td><td>Парацетамол</td><td>325 мг</td><td><span class="status-indicator not-completed"></span>Отказ: аллергия</td><td><button class="btn btn-success btn-sm" onclick="markCompleted(this)">Принято</button><button class="btn btn-danger btn-sm" onclick="showReason(this)">Отказ</button><input type="text" class="form-control mt-1 reason-input" placeholder="Причина отказа..." style="display:none;"></td></tr>
                                <tr><td>16:00</td><td>Метронидазол</td><td>500 мг</td><td><span class="status-indicator pending"></span>Ожидает</td><td><button class="btn btn-success btn-sm" onclick="markCompleted(this)">Принято</button><button class="btn btn-danger btn-sm" onclick="showReason(this)">Отказ</button><input type="text" class="form-control mt-1 reason-input" placeholder="Причина отказа..." style="display:none;"></td></tr>
                                <tr><td>20:00</td><td>Флуконазол</td><td>150 мг</td><td><span class="status-indicator pending"></span>Ожидает</td><td><button class="btn btn-success btn-sm" onclick="markCompleted(this)">Принято</button><button class="btn btn-danger btn-sm" onclick="showReason(this)">Отказ</button><input type="text" class="form-control mt-1 reason-input" placeholder="Причина отказа..." style="display:none;"></td></tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Манипуляции -->
                        <div id="procedures" class="tab-content">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>Время</th>
                                    <th>Манипуляция</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr><td>07:30</td><td>Забор крови на биохимию</td><td><span class="status-indicator completed"></span>Выполнено</td><td><button class="btn btn-success btn-sm" onclick="markCompleted(this)">Принято</button><button class="btn btn-danger btn-sm" onclick="showReason(this)">Отказ</button><input type="text" class="form-control mt-1 reason-input" placeholder="Причина отказа..." style="display:none;"></td></tr>
                                <tr><td>09:00</td><td>Инъекция цефтриаксона</td><td><span class="status-indicator completed"></span>Выполнено</td><td><button class="btn btn-success btn-sm" onclick="markCompleted(this)">Принято</button><button class="btn btn-danger btn-sm" onclick="showReason(this)">Отказ</button><input type="text" class="form-control mt-1 reason-input" placeholder="Причина отказа..." style="display:none;"></td></tr>
                                <tr><td>11:00</td><td>Катетеризация вены</td><td><span class="status-indicator pending"></span>Ожидает</td><td><button class="btn btn-success btn-sm" onclick="markCompleted(this)">Принято</button><button class="btn btn-danger btn-sm" onclick="showReason(this)">Отказ</button><input type="text" class="form-control mt-1 reason-input" placeholder="Причина отказа..." style="display:none;"></td></tr>
                                <tr><td>14:00</td><td>Перевязка раны</td><td><span class="status-indicator not-completed"></span>Отказ: пациент отказался</td><td><button class="btn btn-success btn-sm" onclick="markCompleted(this)">Принято</button><button class="btn btn-danger btn-sm" onclick="showReason(this)">Отказ</button><input type="text" class="form-control mt-1 reason-input" placeholder="Причина отказа..." style="display:none;"></td></tr>
                                <tr><td>17:00</td><td>Промывание катетера</td><td><span class="status-indicator pending"></span>Ожидает</td><td><button class="btn btn-success btn-sm" onclick="markCompleted(this)">Принято</button><button class="btn btn-danger btn-sm" onclick="showReason(this)">Отказ</button><input type="text" class="form-control mt-1 reason-input" placeholder="Причина отказа..." style="display:none;"></td></tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Исследования -->
                        <div id="researches" class="tab-content">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>Время</th>
                                    <th>Тип</th>
                                    <th>Исследование</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr><td>10:00</td><td>Лабораторное</td><td>Общий анализ крови</td><td><span class="status-indicator pending"></span>Назначено</td><td><button class="btn btn-success btn-sm" onclick="markCompleted(this)">Принято</button><button class="btn btn-danger btn-sm" onclick="showReason(this)">Отказ</button><input type="text" class="form-control mt-1 reason-input" placeholder="Причина отказа..." style="display:none;"></td></tr>
                                <tr><td>13:00</td><td>Инструментальное</td><td>УЗИ брюшной полости</td><td><span class="status-indicator pending"></span>Ожидает</td><td><button class="btn btn-success btn-sm" onclick="markCompleted(this)">Принято</button><button class="btn btn-danger btn-sm" onclick="showReason(this)">Отказ</button><input type="text" class="form-control mt-1 reason-input" placeholder="Причина отказа..." style="display:none;"></td></tr>
                                <tr><td>15:00</td><td>Инструментальное</td><td>Рентген легких</td><td><span class="status-indicator pending"></span>Ожидает</td><td><button class="btn btn-success btn-sm" onclick="markCompleted(this)">Принято</button><button class="btn btn-danger btn-sm" onclick="showReason(this)">Отказ</button><input type="text" class="form-control mt-1 reason-input" placeholder="Причина отказа..." style="display:none;"></td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="save-btn mt-3">
                            <button class="btn btn-primary"><i class="fas fa-save"></i> Сохранить изменения</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="recommendActionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Действия</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <div id="recommendation-content">Загрузка...</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@php
    $jsChartData = collect($chartData)->map(function($item) {
        $item['t'] = \Carbon\Carbon::parse($item['t'])->timestamp * 1000;
        return $item;
    })->toArray();
@endphp

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        $(function () {
            const records = @json($jsChartData);

            // Функция для создания опций графика
            function getChartOptions(title, series, colors, yFormatter = null) {
                const options = {
                    chart: {
                        type: 'line',
                        height: 350,
                        toolbar: {
                            show: false,
                            tools: {
                                zoom: true,
                                zoomin: true,
                                zoomout: true,
                                pan: true,
                                reset: true,
                            }
                        },
                        zoom: {
                            enabled: true,
                            type: 'x', // можно x, y или xy
                            autoScaleYaxis: true // автоматически масштабирует ось Y при зуме
                        },
                    },
                    title: {
                        text: title,
                        align: 'center',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold'
                        }
                    },
                    colors: colors,
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    markers: {
                        size: 4,
                        hover: {
                            sizeOffset: 3
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    legend: {
                        position: 'top',
                        fontSize: '14px',
                    },
                    grid: {
                        borderColor: '#eee',
                        strokeDashArray: 4
                    },
                    tooltip: {
                        x: {
                            format: 'dd.MM.yyyy HH:mm'
                        }
                    },
                    xaxis: {
                        type: 'datetime',
                        title: {
                            text: 'Дата и время'
                        }
                    },
                    yaxis: [
                        {
                            labels: {style: {colors: '#FF7043'}},
                            axisTicks: {show: true},
                            axisBorder: {show: true, color: '#FF7043'}
                        },
                        {
                            opposite: true,
                            labels: {style: {colors: '#29B6F6'}},
                            axisTicks: {show: true},
                            axisBorder: {show: true, color: '#29B6F6'}
                        },
                        {
                            labels: {style: {colors: '#66BB6A'}},
                            axisTicks: {show: true},
                            axisBorder: {show: true, color: '#66BB6A'}
                        },
                        {
                            opposite: true,
                            labels: {style: {colors: '#AB47BC'}},
                            axisTicks: {show: true},
                            axisBorder: {show: true, color: '#AB47BC'}
                        },
                        {
                            labels: {style: {colors: '#FFA726'}},
                            axisTicks: {show: true},
                            axisBorder: {show: true, color: '#FFA726'}
                        }
                    ]
                    ,

                    series: series
                };

                if (yFormatter) {
                    options.yaxis.labels = {
                        formatter: yFormatter
                    };
                }

                return options;
            }

            // Функция проверки данных
            function hasData(key) {
                return records.some(r => r[key] !== null);
            }

            // Переключение меню
            $('#parameter-menu button').on('click', function () {
                const target = $(this).data('target');
                $('.chart-section').addClass('d-none');

                if (target === 'breathing_rate' && !hasData('breathing_rate')) {
                    $(`#${target}`).html('<p class="text-center p-4">Нет данных для ЧДД</p>').removeClass('d-none');
                } else if (target === 'oxygen_type' && !hasData('oxygen_type')) {
                    $(`#${target}`).html('<p class="text-center p-4">Нет данных для Кислород/воздух</p>').removeClass('d-none');
                } else {
                    $(`#${target}`).removeClass('d-none');
                }

                $('#parameter-menu button').removeClass('active');
                $(this).addClass('active');
            });

            // Инициализация графиков
            try {
                // Общий график
                const generalSeries = [
                    {
                        name: 'Систолическое АД',
                        data: records.filter(r => r.systolic !== null).map(r => [r.t, r.systolic])
                    },
                    {
                        name: 'Диастолическое АД',
                        data: records.filter(r => r.diastolic !== null).map(r => [r.t, r.diastolic])
                    },
                    {
                        name: 'Пульс',
                        data: records.filter(r => r.pulse !== null).map(r => [r.t, r.pulse])
                    },
                    {
                        name: 'Температура',
                        data: records.filter(r => r.temperature !== null).map(r => [r.t, r.temperature])
                    },
                    {
                        name: 'Сатурация',
                        data: records.filter(r => r.saturation !== null).map(r => [r.t, r.saturation])
                    }
                ];

                new ApexCharts(document.querySelector('#generalChart'), getChartOptions(
                    'Общие жизненные показатели',
                    generalSeries,
                    ['#FF7043', '#29B6F6', '#66BB6A', '#AB47BC', '#FFA726']
                )).render();

                // График ЧДД
                if (hasData('breathing_rate')) {
                    new ApexCharts(document.querySelector('#breathingChart'), getChartOptions(
                        'Частота дыхания (ЧДД)',
                        [{
                            name: 'ЧДД',
                            data: records
                                .filter(r => r.breathing_rate !== null)
                                .map(r => [r.t, r.breathing_rate])
                        }],
                        ['#42A5F5']
                    )).render();
                }

                // График кислорода
                if (hasData('oxygen_type')) {
                    const oxygenOptions = getChartOptions(
                        'Кислород/воздух',
                        [{
                            name: 'Тип подачи кислорода',
                            data: records
                                .filter(r => r.oxygen_type !== null)
                                .map(r => [r.t, r.oxygen_type])
                        }],
                        ['#FF7043'],
                    );

                    new ApexCharts(document.querySelector('#oxygenChart'), oxygenOptions).render();
                }

                // График сознания
                if (hasData('consciousness')) {
                    new ApexCharts(document.querySelector('#consciousnessChart'), getChartOptions(
                        'Уровень сознания',
                        [{
                            name: 'Уровень сознания',
                            data: records
                                .filter(r => r.consciousness !== null)
                                .map(r => [r.t, r.consciousness])
                        }],
                        ['#AB47BC']
                    )).render();
                }

                // График общего балла
                if (hasData('total_score')) {
                    new ApexCharts(document.querySelector('#totalChart'), getChartOptions(
                        'Общий балл',
                        [{
                            name: 'Общий балл',
                            data: records
                                .filter(r => r.total_score !== null)
                                .map(r => [r.t, r.total_score])
                        }],
                        ['#66BB6A']
                    )).render();
                }

            } catch (e) {
                console.error('Ошибка инициализации графиков:', e);
                $('#general').html('<p class="text-danger text-center p-4">Ошибка при создании графиков: ' + e.message + '</p>');
            }
        });

        // treatment plan modal
        // Переключение между табами
        function switchTab(tabId) {
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });

            document.querySelector(`.tab[onclick="switchTab('${tabId}')"]`).classList.add('active');
            document.getElementById(tabId).classList.add('active');
        }

        // Отметка как выполненного
        function markCompleted(button) {
            const row = button.closest('tr');
            const statusCell = row.querySelector('.status');

            statusCell.innerHTML = `
                <span class="status-indicator completed"></span>
                ${statusCell.textContent.includes('Отказ') ? 'Выполнено' : statusCell.textContent.replace('Ожидает', 'Выполнено')}
            `;

            // Скрываем поле для причины отказа
            const reasonInput = row.querySelector('.reason-input');
            reasonInput.style.display = 'none';
        }

        // Показать поле для причины отказа
        function showReason(button) {
            const row = button.closest('tr');
            const statusCell = row.querySelector('.status');
            const reasonInput = row.querySelector('.reason-input');

            reasonInput.style.display = 'block';
            reasonInput.focus();

            reasonInput.onblur = function () {
                if (reasonInput.value.trim() !== '') {
                    statusCell.innerHTML = `
                        <span class="status-indicator not-completed"></span>
                        Отказ: ${reasonInput.value}
                    `;
                }
                reasonInput.style.display = 'none';
            };
        }

        // Сортировка таблиц по времени при загрузке
        document.addEventListener('DOMContentLoaded', function () {
            sortTableByTime('medications');
            sortTableByTime('procedures');
        });

        function sortTableByTime(tabId) {
            const table = document.getElementById(tabId).querySelector('tbody');
            const rows = Array.from(table.querySelectorAll('tr'));

            rows.sort((a, b) => {
                const timeA = a.cells[0].textContent;
                const timeB = b.cells[0].textContent;
                return timeA.localeCompare(timeB);
            });

            // Очищаем и перезаполняем таблицу
            table.innerHTML = '';
            rows.forEach(row => table.appendChild(row));
        }


        // document score
        $(document).on('show.bs.modal', '#recommendActionModal', function (event) {
            const button = $(event.relatedTarget);
            const documentId = button.data('document-id');
            const responseId = button.data('response-id');
            const $modal = $('#recommendActionModal');
            const $container = $('#recommendation-content');

            // Сохраняем responseId
            $modal.data('response-id', responseId);

            $container.html('Загрузка...');

            $.ajax({
                url: `/api/document-scores/${documentId}/${responseId}`,
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    if (data.actions && data.actions.length > 0) {
                        const finishedIds = data.finished.map(el => el.document_action_id);

                        const listUnfinished = data.actions
                            .filter(action => !finishedIds.includes(action.id))
                            .map(action => `
                <li class="d-flex justify-content-between align-items-center mb-2">
                    <span>${action.action}</span>
                    <button class="btn btn-sm btn-success mark-done-btn"
                            data-action-id="${action.id}"
                            data-score-id="${data.id}">
                        ✔️
                    </button>
                </li>
            `).join('');

                        const listFinished = data.actions
                            .filter(action => finishedIds.includes(action.id))
                            .map(action => `
                <li class="d-flex justify-content-between align-items-center mb-2">
                    <span>${action.action}</span>
                </li>
            `).join('');

                        $container.html(`
            <div>
                <h6>Рекомендованные действия:</h6>
                <ul id="recommendation-list">${listUnfinished || '<li>Все действия выполнены.</li>'}</ul>
                <hr>
                <h6>Выполненные действия:</h6>
                <ul id="done-list" class="text-muted" style="font-style: italic;">
                    ${listFinished || '<li>Нет выполненных действий.</li>'}
                </ul>
            </div>
        `);
                    } else {
                        $container.html('Рекомендации не найдены.');
                    }
                },
                error: function () {
                    $container.html('Ошибка при загрузке рекомендаций.');
                }
            });
        });

        $(document).on('click', '.mark-done-btn', function () {
            const $btn = $(this);
            const $item = $btn.closest('li');
            const $modal = $('#recommendActionModal');
            const responseId = $modal.data('response-id');
            const scoreId = $btn.data('score-id');
            const actionId = $btn.data('action-id');

            $.ajax({
                url: '/api/document/finish/action',
                method: 'POST',
                data: {
                    document_response_id: responseId,
                    document_score_id: scoreId,
                    document_action_id: actionId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function () {
                    $btn.remove();
                    $('#done-list').append($item);
                },
                error: function () {
                    alert('Ошибка при сохранении. Повторите попытку.');
                }
            });
        });
    </script>
@endsection
