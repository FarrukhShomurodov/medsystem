@extends('admin.layouts.app')
@use('App\Support\Display')

@php
    $patientName = $mapDetails['patient']['full_name'];
    $canOpenPatient = auth()->user()?->can('view_patients');
    $admission = $mapDetails['admission'];
    $diagnosis = fn (array $diagnosis) => collect([$diagnosis['code'] ?? null, $diagnosis['description'] ?? null])->filter()->implode(' — ');
    $responses = $mapDetails['examinations']['surveyResponses']->sortByDesc('id')->take(5);
@endphp

@section('title', "Карта обследования №{$mapDetails['id']}")

@section('header')
    <x-page-header :title="$patientName" :breadcrumbs="[
        'Пациенты' => auth()->user()?->can('list_patients') ? route('patients.index') : null,
    ]">
        <x-slot:actions>
            @if ($canOpenPatient)
                <a href="{{ route('patients.show', $mapDetails['patient']['id']) }}" class="btn">
                    <i class="bx bx-user" aria-hidden="true"></i>Карточка пациента
                </a>
            @endif
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    <div class="row row-cards">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h2 class="card-title">Основная информация</h2></div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">ID карты</div>
                            <div class="datagrid-content">{{ $mapDetails['id'] }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Пациент</div>
                            <div class="datagrid-content">
                                @if ($canOpenPatient)
                                    <a href="{{ route('patients.show', $mapDetails['patient']['id']) }}">{{ $patientName }}</a>
                                @else
                                    {{ $patientName }}
                                @endif
                                <span class="text-secondary">(ID: {{ $mapDetails['patient']['id'] }})</span>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Дата создания</div>
                            <div class="datagrid-content">{{ $mapDetails['created_at'] }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Лечащий врач</div>
                            <div class="datagrid-content">
                                {{ $mapDetails['doctor']['name'] }}
                                @if ($mapDetails['doctor']['speciality'])
                                    <span class="text-secondary">({{ $mapDetails['doctor']['speciality'] }})</span>
                                @endif
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Учреждение</div>
                            <div class="datagrid-content">{{ $mapDetails['department']['name'] }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Форма лечения</div>
                            <div class="datagrid-content">{{ $mapDetails['is_outpatient'] ? 'Амбулаторно' : 'Стационарно' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h2 class="card-title">Госпитализация</h2></div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Дата госпитализации</div>
                            <div class="datagrid-content">{{ $admission['date'] ? Display::date($admission['date']) : 'Не указано' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Дата выписки</div>
                            <div class="datagrid-content">{{ $admission['discharge_date'] ? Display::date($admission['discharge_date']) : 'Не указано' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Диагноз при госпитализации</div>
                            <div class="datagrid-content">{{ $diagnosis($admission['admission_diagnosis']) ?: '—' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Диагноз при выписке</div>
                            <div class="datagrid-content">{{ $diagnosis($admission['discharge_diagnosis']) ?: '—' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Цель обследования</div>
                            <div class="datagrid-content">{{ $admission['purpose'] ?: '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h2 class="card-title">Анамнез</h2></div>
                <div class="card-body">
                    <div class="datagrid">
                        @foreach (['complaints' => 'Жалобы', 'disease_history' => 'История заболевания', 'general_anamnesises' => 'Общий анамнез'] as $key => $label)
                            <div class="datagrid-item">
                                <div class="datagrid-title">{{ $label }}</div>
                                <div class="datagrid-content">{{ $mapDetails['anamnesis'][$key] ?: '—' }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h2 class="card-title">Услуги</h2></div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead>
                        <tr>
                            <th class="w-1">ID</th>
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
                        @forelse ($mapDetails['examinations']['services'] as $service)
                            <tr>
                                <td class="text-secondary">{{ $service->id }}</td>
                                <td>{{ $service->service->name }}</td>
                                <td class="text-nowrap">{{ Display::date($service->service_date) }}</td>
                                <td>{{ $service->service_time }}</td>
                                <td>{{ $service->place?->label() }}</td>
                                <td>{{ $service->result }}</td>
                                <td><x-status-badge :status="$service->status" class="me-1" /></td>
                                <td>{{ $service->paymentStatus()?->label() }}</td>
                                <td>{{ $service->insurance_policy['company_name'] ?? '' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9"><x-empty-state title="Услуг нет" icon="bx-clipboard" /></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h2 class="card-title">Мед. документы</h2></div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead>
                        <tr>
                            <th class="w-1">ID</th>
                            <th>Документ</th>
                            <th class="w-1"><span class="visually-hidden">Действия</span></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($responses as $response)
                            <tr class="survey-row survey-color-{{ ltrim(strtolower((string) $response->survey->color), '#') }}">
                                <td class="text-secondary">{{ $response->id }}</td>
                                <td>
                                    <span class="survey-swatch me-2" aria-hidden="true"></span>{{ $response->survey->title }}
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm view-response"
                                            data-url="{{ route('api.survey-responses.show', $response->id) }}"
                                            data-bs-toggle="modal" data-bs-target="#responseModal">
                                        Просмотр
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><x-empty-state title="Документов нет" icon="bx-file" /></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if ($responses->isNotEmpty())
        @include('admin.maps.partials.response-modal', ['title' => 'Ответы документа'])
    @endif
@endsection

@section('scripts')
    @vite('resources/js/pages/map-show.js')
@endsection
