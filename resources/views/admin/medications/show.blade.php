@extends('admin.layouts.app')
@use('App\Support\Display')

@php
    $composition = $medication->composition;
    $application = $medication->application;
    $license = $medication->licenseAndCertification;
    $data = $medication->additionalData;

    $sections = [
        'Основные данные' => [
            'Название:' => $medication->name,
            'Форма выпуска:' => $medication->release_form,
            'Дозировка:' => $medication->dosage,
            'Фармакологическая группа:' => $medication->group?->name,
            'Производитель:' => $medication->manufacturer?->name,
            'Регистрационный номер:' => $medication->registration_number,
            'Дата регистрации:' => Display::date($medication->registration_date),
        ],
        'Применение' => [
            'Показания к применению:' => $application?->indications,
            'Способ применения:' => $application?->administration_method,
            'Режим дозирования:' => $application?->dosage_regimen,
            'Взаимодействие с другими препаратами:' => $application?->drug_interactions,
        ],
        'Хранение' => [
            'Температурный режим хранения:' => $medication->storage?->storage_temperature,
            'Условия хранения:' => $medication->storage?->storage_conditions,
        ],
        'Прочие данные' => [
            'Номер лицензии на производство:' => $data?->manufacturing_license_number,
            'Дополнительные условия использования:' => $data?->additional_usage_conditions,
            'Категория рецепта:' => $data?->prescription_category,
            'Индивидуальные особенности:' => $data?->specific_features,
        ],
    ];

    $documents = array_filter([
        'Лицензия на продажу' => $license?->sales_license,
        'Сертификат качества' => $license?->quality_certificate,
        'Клинические исследования' => $license?->clinical_trials,
        'Регистрационные документы' => $license?->registration_documents,
    ]);
@endphp

@section('title', $medication->name)

@section('header')
    <x-page-header :title="$medication->name" :breadcrumbs="['Препараты' => route('medications.index')]" />
@endsection

@section('content')
    <div class="row row-cards">
        @foreach ($sections as $heading => $rows)
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h2 class="card-title">{{ $heading }}</h2>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            @foreach ($rows as $label => $value)
                                <dt class="col-sm-5 fw-medium text-secondary">{{ $label }}</dt>
                                <dd class="col-sm-7">{{ $value ?? '—' }}</dd>
                            @endforeach
                        </dl>
                    </div>
                </div>
            </div>

            @if ($loop->first)
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h2 class="card-title">Состав</h2>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-5 fw-medium text-secondary">Название активного вещества:</dt>
                                <dd class="col-sm-7">{{ $composition?->active_substance_name ?? '—' }}</dd>
                                <dt class="col-sm-5 fw-medium text-secondary">Дозировка активного вещества:</dt>
                                <dd class="col-sm-7">{{ $composition?->active_substance_dosage ?? '—' }}</dd>
                            </dl>
                            <h3 class="subheader mb-2">Вспомогательные вещества</h3>
                            @if (! $composition || $composition->additionalSubstance->isEmpty())
                                <p class="text-secondary mb-0">Не указаны.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-vcenter mb-0">
                                        <thead>
                                        <tr>
                                            <th class="w-1">#</th>
                                            <th>Название</th>
                                            <th>Дозировка</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($composition->additionalSubstance as $substance)
                                            <tr>
                                                <td class="text-secondary">{{ $loop->iteration }}</td>
                                                <td>{{ $substance->substance_name }}</td>
                                                <td>{{ $substance->substance_dosage }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h2 class="card-title">Лицензии и сертификация</h2>
                </div>
                <div class="card-body">
                    @forelse ($documents as $label => $path)
                        <div @class(['d-flex align-items-center gap-2', 'mt-2' => ! $loop->first])>
                            <span class="fw-medium text-secondary">{{ $label }}:</span>
                            <a href="{{ asset('storage/'.$path) }}" target="_blank" rel="noopener" class="btn btn-sm ms-auto">
                                <i class="bx bx-download" aria-hidden="true"></i>Скачать
                            </a>
                        </div>
                    @empty
                        <p class="text-secondary mb-0">Документы не загружены.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
