@extends('admin.layouts.app')
@use('App\Support\Display')

@php
    $fullName = $patient->full_name;
    // Sections of the vital signs panel (NurseWindowController::MENU): icon and colour of each.
    $sectionIcons = [
        'general' => ['bx-line-chart', 'bg-green-lt'],
        'breathing_rate' => ['bx-line-chart', 'bg-azure-lt'],
        'consciousness' => ['bx-brain', 'bg-purple-lt'],
        'oxygen_type' => ['bx-wind', 'bg-orange-lt'],
        'total_score' => ['bx-star', 'bg-red-lt'],
    ];
    $journalColumns = [
        'systolic' => 'Систолическое АД', 'diastolic' => 'Диастолическое АД', 'pulse' => 'Пульс',
        'temperature' => 'Температура', 'saturation' => 'Сатурация', 'breathing_rate' => 'ЧДД',
        'consciousness' => 'Сознание', 'oxygen_type' => 'Кислород/воздух', 'urine_balance' => 'Диурез',
        'total_score' => 'Общий балл',
    ];
    $responses = $examinationMap->documentResponses;
@endphp

@section('title', "Сестринское окно: {$fullName}")

@section('header')
    <x-page-header :title="$fullName" :breadcrumbs="['Сестринское окно' => route('sister.window.index')]">
        <x-slot:actions>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addParameterModal">
                <i class="bx bx-plus" aria-hidden="true"></i>Добавить данные
            </button>
            <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#treatmentPlanModal">
                <i class="bx bx-list-check" aria-hidden="true"></i>План лечения
            </button>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    <script type="application/json" id="vital-signs-data">@json($jsChartData)</script>

    <div class="row row-cards" id="nurse-window"
         data-actions-url="{{ route('api.document-scores.actions', ['document' => '__document__', 'response' => '__response__']) }}"
         data-finish-url="{{ route('api.document-actions.finish') }}">
        <div class="col-12">
            @include('admin.sister_window.partials.patients', ['patients' => $patients, 'current' => $patient->id])
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h2 class="card-title">Показатели</h2></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="nav flex-column nav-pills gap-1" role="tablist" aria-orientation="vertical" aria-label="Показатели">
                                @foreach ($menu as $key => $item)
                                    @php [$icon, $tone] = $sectionIcons[$key] ?? ['bx-line-chart', 'bg-secondary-lt']; @endphp
                                    <button type="button" id="vital-tab-{{ $key }}" role="tab"
                                            @class(['nav-link d-flex align-items-center gap-2 text-start', 'active' => $loop->first])
                                            data-bs-toggle="pill" data-bs-target="#vital-{{ $key }}" aria-controls="vital-{{ $key }}"
                                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                        <span class="avatar avatar-sm {{ $tone }}" aria-hidden="true"><i class="bx {{ $icon }}"></i></span>
                                        {{ $item['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="tab-content">
                                @foreach ($menu as $key => $item)
                                    <div id="vital-{{ $key }}" role="tabpanel" aria-labelledby="vital-tab-{{ $key }}" tabindex="0"
                                         @class(['tab-pane fade', 'show active' => $loop->first])>
                                        <div class="vital-chart" data-chart="{{ $key }}"></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h2 class="card-title">Журнал</h2></div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead>
                        <tr>
                            <th>Дата / время</th>
                            @foreach ($journalColumns as $columnLabel)
                                <th>{{ $columnLabel }}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($records as $record)
                            <tr>
                                <td class="text-nowrap">{{ $record->datetime }}</td>
                                @foreach (array_keys($journalColumns) as $column)
                                    <td>{{ $record->{$column} }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($journalColumns) + 1 }}"><x-empty-state title="Измерений пока нет" icon="bx-pulse" /></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h2 class="card-title">Документы</h2></div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead>
                        <tr>
                            <th>Документ</th>
                            @foreach ($responses as $response)
                                <th class="text-nowrap">{{ Display::date($response->created_at) }}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($documents as $document)
                            <tr>
                                <td>
                                    <a href="{{ route('documents.show', ['document' => $document->id, 'patient_id' => $patient->id]) }}">
                                        <strong>{{ $document->title }}</strong>
                                    </a>
                                </td>
                                @foreach ($responses as $response)
                                    <td>
                                        @if ($response->document_id == $document->id)
                                            <button type="button" class="btn btn-sm btn-ghost-primary"
                                                    data-bs-toggle="modal" data-bs-target="#recommendActionModal"
                                                    data-document-id="{{ $response->document_id }}" data-response-id="{{ $response->id }}"
                                                    aria-label="Рекомендованные действия: {{ $document->title }}, {{ $response->total_score }} баллов">
                                                {{ $response->total_score }}
                                            </button>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-blur fade" id="addParameterModal" tabindex="-1" aria-labelledby="addParameterModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="addParameterModalTitle">Добавить данные пациента</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <form action="{{ route('patient.vital.parameters.store', $patient->id) }}" method="POST" class="modal-form">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            @foreach ($vitalParameters as $param)
                                <div class="col-md-6">
                                    <label for="param_{{ $param->id }}" class="form-label">{{ $param->name }}</label>
                                    <input type="number" step="0.1" name="parameters[{{ $param->id }}]" id="param_{{ $param->id }}"
                                           class="form-control @error('parameters.'.$param->id) is-invalid @enderror"
                                           value="{{ old('parameters.'.$param->id) }}">
                                    @error('parameters.'.$param->id)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endforeach
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

    <div class="modal modal-blur fade" id="treatmentPlanModal" tabindex="-1" aria-labelledby="treatmentPlanModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="treatmentPlanModalTitle">План лечения пациента</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    {{-- The orders of the open stay: every order made during a stay is attached to it. --}}
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        @foreach (['medications' => 'Лист назначения', 'procedures' => 'Манипуляции', 'researches' => 'Исследования'] as $tab => $tabLabel)
                            <li class="nav-item" role="presentation">
                                <button type="button" id="plan-tab-{{ $tab }}" role="tab" @class(['nav-link', 'active' => $loop->first])
                                        data-bs-toggle="tab" data-bs-target="#{{ $tab }}" aria-controls="{{ $tab }}"
                                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $tabLabel }}</button>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content">
                        <div id="medications" class="tab-pane fade show active" role="tabpanel" aria-labelledby="plan-tab-medications" tabindex="0">
                            <div class="table-responsive">
                                <table class="table table-vcenter">
                                    <thead>
                                    <tr>
                                        <th class="w-1">#</th>
                                        <th>Дата и время назначения</th>
                                        <th>Препарат, доза, правило и время приёма (ч:мин)</th>
                                        <th>Дата</th>
                                        <th>Статус</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($examinationMap->patientMedications as $prescription)
                                        <tr>
                                            <td class="text-secondary">{{ $loop->iteration }}</td>
                                            <td class="text-nowrap">{{ \Carbon\Carbon::parse($prescription->created_at)->format('d.m.Y H:i') }}</td>
                                            <td>
                                                {{ $prescription->medication->name }}, {{ $prescription->dosage }}, {{ $prescription->admission_rules->label() }}
                                                <div class="tag-list mt-1">
                                                    @foreach (array_filter(explode(',', (string) $prescription->admission_period)) as $time)
                                                        <span class="badge bg-primary-lt">{{ $time }}</span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="text-nowrap">{{ Display::date($prescription->date_of_end) }}</td>
                                            <td class="text-nowrap">
                                                <x-status-icon :status="$prescription->status" decorative />
                                                {{ $prescription->status->label() }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5"><x-empty-state title="Препараты не назначены" icon="bx-capsule" /></td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="procedures" class="tab-pane fade" role="tabpanel" aria-labelledby="plan-tab-procedures" tabindex="0">
                            <div class="table-responsive">
                                <table class="table table-vcenter">
                                    <thead>
                                    <tr>
                                        <th>Дата</th>
                                        <th>Манипуляция</th>
                                        <th>Статус</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($examinationMap->patientProcedures as $order)
                                        <tr>
                                            <td class="text-nowrap">{{ $order->procedure_date ? \Carbon\Carbon::parse($order->procedure_date)->format('d.m.Y') : '—' }}</td>
                                            <td>{{ $order->procedure->name }}</td>
                                            <td><x-status-badge :status="$order->status" /></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3"><x-empty-state title="Манипуляции не назначены" icon="bx-injection" /></td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="researches" class="tab-pane fade" role="tabpanel" aria-labelledby="plan-tab-researches" tabindex="0">
                            <div class="table-responsive">
                                <table class="table table-vcenter">
                                    <thead>
                                    <tr>
                                        <th>Дата и время</th>
                                        <th>Исследование</th>
                                        <th>Статус</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($examinationMap->services as $order)
                                        <tr>
                                            <td class="text-nowrap">
                                                {{ $order->service_date ? \Carbon\Carbon::parse($order->service_date)->format('d.m.Y') : '—' }}
                                                {{ $order->service_time ? \Carbon\Carbon::parse($order->service_time)->format('H:i') : '' }}
                                            </td>
                                            <td>{{ $order->service->name }}</td>
                                            <td><x-status-badge :status="$order->status" /></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3"><x-empty-state title="Исследования не назначены" icon="bx-test-tube" /></td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-blur fade" id="recommendActionModal" tabindex="-1" aria-labelledby="recommendActionModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="recommendActionModalTitle">Рекомендованные действия</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body" id="recommendation-content" aria-live="polite"></div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite('resources/js/pages/nurse-window.js')
@endsection
