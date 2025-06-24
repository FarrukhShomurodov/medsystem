@extends('admin.layouts.app')

@section('content')
    <div class="card mt-3">
        <div class="card-header">
            <h5>Пациенты</h5>
        </div>
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
                <tr class="patient-row cursor-pointer @if($patient->id == $p->id)bg-warning text-dark @endif"
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


    {{-- Контейнер со страницей --}}
    <div class="card mt-4">
        <div class="card-header">
            <div class="text-end">
                <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal"
                        data-bs-target="#addParameterModal">
                    Добавить данные
                </button>
            </div>
        </div>

        <div class="row card-body">
            {{-- Меню слева --}}
            <div class="col-md-3">
                <div class="list-group mb-4" id="parameter-menu">
                    @foreach($menu as $key => $item)
                        <button
                            class="list-group-item list-group-item-action @if($loop->first) active @endif"
                            data-target="{{ $key }}"
                            style="@if($item['graphic']) border-left: 4px solid green; @endif"
                        >
                            {{ $item['label'] }}
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
        <div class="card-header">
            <h5>Журнал данных</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Дата/Время</th>
                    <th>Сист/Диаст</th>
                    <th>Пульс</th>
                    <th>Темп.</th>
                    <th>Сатур.</th>
                    <th>ЧДД</th>
                    <th>Сознание</th>
                    <th>Кислород</th>
                    <th>Общий балл</th>
                </tr>
                </thead>
                <tbody>
                @foreach($records as $r)
                    <tr>
                        <td>{{ $r->created_at->format('d.m.Y H:i') }}</td>
                        <td>{{ $r->systolic }}/{{ $r->diastolic }}</td>
                        <td>{{ $r->pulse }}</td>
                        <td>{{ $r->temperature }}</td>
                        <td>{{ $r->saturation }}</td>
                        <td>{{ $r->breathing_rate }}</td>
                        <td>{{ $r->consciousness }}</td>
                        <td>{{ $r->oxygen_type }}</td>
                        <td>{{ $r->total_score }}</td>
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
                <form action="{{ route('patient.parameters.store', $patient->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="patient_id" value="{{ $patient->id }}">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="systolic" class="form-label">Систолическое АД</label>
                                <input type="number" step="1" name="systolic" id="systolic" class="form-control"
                                       value="{{ old('systolic') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="diastolic" class="form-label">Диастолическое АД</label>
                                <input type="number" step="1" name="diastolic" id="diastolic" class="form-control"
                                       value="{{ old('diastolic') }}">
                            </div>

                            <div class="col-md-4">
                                <label for="pulse" class="form-label">Пульс</label>
                                <input type="number" name="pulse" id="pulse" class="form-control"
                                       value="{{ old('pulse') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="temperature" class="form-label">Температура</label>
                                <input type="number" step="0.1" name="temperature" id="temperature" class="form-control"
                                       value="{{ old('temperature') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="saturation" class="form-label">Сатурация (SPO₂)</label>
                                <input type="number" name="saturation" id="saturation" class="form-control"
                                       value="{{ old('saturation') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="breathing_rate" class="form-label">Частота дыхания (ЧДД)</label>
                                <input type="number" name="breathing_rate" id="breathing_rate" class="form-control"
                                       value="{{ old('breathing_rate') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="consciousness" class="form-label">Уровень сознания</label>
                                <input type="text" name="consciousness" id="consciousness" class="form-control"
                                       value="{{ old('consciousness') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="oxygen_type" class="form-label">Кислород/воздух</label>
                                <input type="text" name="oxygen_type" id="oxygen_type" class="form-control"
                                       value="{{ old('oxygen_type') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="pain_score" class="form-label">Оценка боли</label>
                                <input type="number" name="pain_score" id="pain_score" class="form-control"
                                       value="{{ old('pain_score') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="fall_risk" class="form-label">Риск падения</label>
                                <input type="number" name="fall_risk" id="fall_risk" class="form-control"
                                       value="{{ old('fall_risk') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="urine_balance" class="form-label">Диурез / Баланс</label>
                                <input type="number" name="urine_balance" id="urine_balance" class="form-control"
                                       value="{{ old('urine_balance') }}">
                            </div>

                            <div class="col-12">
                                <label for="total_score" class="form-label">Общий балл</label>
                                <input type="number" name="total_score" id="total_score" class="form-control"
                                       value="{{ old('total_score') }}">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- Подключаем ApexCharts --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    @php
        $chartData = $records
            ->map(function($r){
                return [
                    't'            => $r->created_at->format('Y-m-d H:i:s'),
                    'systolic'     => $r->systolic,
                    'diastolic'    => $r->diastolic,
                    'pulse'        => $r->pulse,
                    'temperature'  => $r->temperature,
                    'saturation'   => $r->saturation,
                    'breathing'    => $r->breathing_rate,
                    'consciousness'=> $r->consciousness,
                    'oxygen'       => $r->oxygen_type,
                    'total'        => $r->total_score,
                ];
            })
            ->toArray();
    @endphp

    <script>
        // Чистый JSON без лишних скобок
        const records = @json($chartData);

        // Переключение секций (без изменений)...
        document.querySelectorAll('#parameter-menu button').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.chart-section').forEach(s => s.classList.add('d-none'));
                document.getElementById(this.dataset.target).classList.remove('d-none');
                document.querySelectorAll('#parameter-menu button').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Инициализация общих графиков
        new ApexCharts(document.querySelector("#generalChart"), {
            chart: {type: 'line', height: 350},
            series: [
                {name: 'SYS', data: records.map(r => [r.t, r.systolic])},
                {name: 'DIAS', data: records.map(r => [r.t, r.diastolic])},
                {name: 'Пульс', data: records.map(r => [r.t, r.pulse])},
                {name: 'Темп.', data: records.map(r => [r.t, r.temperature])},
                {name: 'Сатур.', data: records.map(r => [r.t, r.saturation])},
            ],
            xaxis: {type: 'datetime'},
            yaxis: [
                {title: {text: 'АД / пульс'}},
                {opposite: true, title: {text: 'Темп./Сатур.'}}
            ]
        }).render();

        // Одиночные графики
        function singleChart(selector, name, key) {
            new ApexCharts(document.querySelector(selector), {
                chart: {type: 'line', height: 300},
                series: [{name: name, data: records.map(r => [r.t, r[key]])}],
                xaxis: {type: 'datetime'}
            }).render();
        }

        singleChart("#breathingChart", "ЧДД", "breathing");
        singleChart("#consciousnessChart", "Сознание", "consciousness");
        singleChart("#oxygenChart", "Кислород", "oxygen");
        singleChart("#totalChart", "Общий балл", "total");
    </script>
@endsection
