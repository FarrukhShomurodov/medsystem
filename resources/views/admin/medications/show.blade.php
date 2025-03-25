@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Препарат </title>
@endsection

@section('content')
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
            <p><strong>Группа:</strong> {{ $medication->group->name }}</p>
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
            <p><strong>Вспомогательные вещества (Название):</strong> {{ $medication->composition->substance_name }}</p>
            <p><strong>Вспомогательные вещества (Дозировка):</strong> {{ $medication->composition->substance_dosage }}
            </p>
            <p><strong>Фармакологическая группа:</strong> {{ $medication->composition->pharmacological_group }}</p>
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
            <p><strong>Противопоказания</strong> {{ $medication->application->contraindications }}</p>
            <p><strong>Особенности применения:</strong> {{ $medication->application->usage_features }}</p>
            <p><strong>Побочные эффекты:</strong> {{ $medication->application->side_effects }}</p>
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
            <p><strong>Специальные условия хранения:</strong> {{ $medication->storage->special_storage_conditions }}</p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Лицензии и сертификация</h5>
        </div>
        <div class="card-body">
            @if (!empty($medication->licenseAndCertification->sales_license))
                <p><strong>Лицензия на продажу:</strong>
                    <a href="{{ asset('storage/' . $medication->licenseAndCertification->sales_license) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-download"></i> Скачать
                    </a>
                </p>
            @endif

            @if (!empty($medication->licenseAndCertification->quality_certificate))
                <p><strong>Сертификат качества:</strong>
                    <a href="{{ asset('storage/' . $medication->licenseAndCertification->quality_certificate) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-download"></i> Скачать
                    </a>
                </p>
            @endif

            @if (!empty($medication->licenseAndCertification->clinical_trials))
                <p><strong>Клинические исследования:</strong>
                    <a href="{{ asset('storage/' . $medication->licenseAndCertification->clinical_trials) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-download"></i> Скачать
                    </a>
                </p>
            @endif

            @if (!empty($medication->licenseAndCertification->registration_documents))
                <p><strong>Регистрационные документы:</strong>
                    <a href="{{ asset('storage/' . $medication->licenseAndCertification->registration_documents) }}" target="_blank" class="btn btn-sm btn-outline-primary">
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
            <p><strong>Номер лицензии на производство:</strong> {{ $medication->additionalData->manufacturing_license_number }}</p>
            <p><strong>Дополнительные условия использования :</strong> {{ $medication->additionalData->additional_usage_conditions }}</p>
            <p><strong>Категория рецепта:</strong> {{ $medication->additionalData->prescription_category }}</p>
            <p><strong>Индивидуальные особенности:</strong> {{ $medication->additionalData->specific_features }}</p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Риск и безопасность</h5>
        </div>
        <div class="card-body">
            <p><strong>Оценка риска:</strong> {{ $medication->riskAndSafety->risk_assessment }}</p>
            <p><strong>Предупреждения:</strong> {{ $medication->riskAndSafety->warnings }}</p>
            <p><strong>Безопасность в использовании:</strong> {{ $medication->riskAndSafety->safety_in_use }}</p>
        </div>
    </div>
@endsection
