@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Препарат </title>
@endsection

@section('content')
    <div class="alert-container position-fixed top-0 end-0 p-3" style="z-index: 10050;">
        @if(session()->has('success'))
            <div class="alert alert-solid-success alert-dismissible d-flex align-items-center" role="alert">
                <i class="bx bx-check-circle fs-4 me-2"></i>
                {{ session()->get('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('medications.index') }}">Препараты</a> /</span> {{ $medication->name }}
    </h6>

    <div class="card">
        <div class="card-header">
            <h5>Основные данные</h5>
        </div>
        <div class="card-body">
            <p><strong>Название:</strong> {{ $medication->name }}</p>
            <p><strong>Форма выпуска:</strong> {{ $medication->release_form }}</p>
            <p><strong>Дозировка:</strong> {{ $medication->dosage }}</p>
            <p><strong>Фармакологическая группа:</strong> {{ $medication->group->name }}</p>
            <p><strong>Производитель:</strong> {{ $medication->manufacturer->name }}</p>
            <p><strong>Регистрационный номер:</strong> {{ $medication->registration_number }}</p>
            <p><strong>Дата регистрации:</strong> {{ $medication->registration_date }}</p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Состав</h5>
        </div>
        <div class="card-body">
            <p><strong>Название активного вещества:</strong> {{ $medication->composition->active_substance_name }}</p>
            <p><strong>Дозировка активного вещества:</strong> {{ $medication->composition->active_substance_dosage }}
            </p>

            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Вспомогательные вещества (Название):</th>
                    <th>Вспомогательные вещества (Дозировка):</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @php
                    $i = 1;
                @endphp

                @foreach($medication->composition->additionalSubstance as $substance)
                    <tr>
                        <td>{{ $i++ }}</td>
                        <td>{{ $substance->substance_name }}</td>
                        <td>{{ $substance->substance_dosage }}</td>
                        <td>
                            <div class="d-inline-block text-nowrap">
                                <form action="{{ route('additional.substance.delete', $substance->id) }}" method="POST"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        Удалить
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Применение</h5>
        </div>
        <div class="card-body">
            <p><strong>Показания к применению:</strong> {{ $medication->application->indications }}</p>
            <p><strong>Способ применения:</strong> {{ $medication->application->administration_method }}</p>
            <p><strong>Режим дозирования:</strong> {{ $medication->application->dosage_regimen }}</p>
            <p><strong>Взаимодействие с другими
                    препаратами:</strong> {{ $medication->application->drug_interactions }}</p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Хранение</h5>
        </div>
        <div class="card-body">
            <p><strong>Температурный режим хранения:</strong> {{ $medication->storage->storage_temperature }}</p>
            <p><strong>Условия хранения:</strong> {{ $medication->storage->storage_conditions }}</p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Лицензии и сертификация</h5>
        </div>
        <div class="card-body">
            @if (!empty($medication->licenseAndCertification->sales_license))
                <p><strong>Лицензия на продажу:</strong>
                    <a href="{{ asset('storage/' . $medication->licenseAndCertification->sales_license) }}"
                       target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-download"></i> Скачать
                    </a>
                </p>
            @endif

            @if (!empty($medication->licenseAndCertification->quality_certificate))
                <p><strong>Сертификат качества:</strong>
                    <a href="{{ asset('storage/' . $medication->licenseAndCertification->quality_certificate) }}"
                       target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-download"></i> Скачать
                    </a>
                </p>
            @endif

            @if (!empty($medication->licenseAndCertification->clinical_trials))
                <p><strong>Клинические исследования:</strong>
                    <a href="{{ asset('storage/' . $medication->licenseAndCertification->clinical_trials) }}"
                       target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-download"></i> Скачать
                    </a>
                </p>
            @endif

            @if (!empty($medication->licenseAndCertification->registration_documents))
                <p><strong>Регистрационные документы:</strong>
                    <a href="{{ asset('storage/' . $medication->licenseAndCertification->registration_documents) }}"
                       target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-download"></i> Скачать
                    </a>
                </p>
            @endif
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Прочие данные</h5>
        </div>
        <div class="card-body">
            <p><strong>Номер лицензии на
                    производство:</strong> {{ $medication->additionalData->manufacturing_license_number }}</p>
            <p><strong>Дополнительные условия использования
                    :</strong> {{ $medication->additionalData->additional_usage_conditions }}</p>
            <p><strong>Категория рецепта:</strong> {{ $medication->additionalData->prescription_category }}</p>
            <p><strong>Индивидуальные особенности:</strong> {{ $medication->additionalData->specific_features }}</p>
        </div>
    </div>
@endsection
