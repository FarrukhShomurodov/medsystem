@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Персонал</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('staffs.index') }}">Персонал</a> /</span> {{ $staff->name }} {{ $staff->last_name }}
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
        <div class="card-header d-flex justify-content-between flex-row align-items-center">
            <h5>Общая информация</h5>

            @php
                $isWorking = $staff->isWorkingToday(now());
            @endphp
            @if ($isWorking)
                <span class="badge bg-success">На работе</span>
            @else
                <span class="badge bg-danger">Не работает</span>
            @endif
        </div>
        <div class="card-body">
            <p><strong>ФИО: </strong> {{ $staff->name }} {{ $staff->last_name }} {{ $staff->middle_name }}</p>
            <p><strong>Дата рождение: </strong> {{ $staff->date_birthday }}</p>
            <p><strong>Пол: </strong> {{ $staff->gender == 'male' ? "Мужчина": "Женщина"}}</p>
            <p><strong>Гражданство : </strong> {{ $staff->citizenship }}</p>
            <p><strong>Emai : </strong> {{ $staff->admin->email }}</p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Контактная информация</h5>
        </div>
        <div class="card-body">
            <p><strong>Телефон: </strong> {{ $staff->contact->phone }}</p>
            <p><strong>Email: </strong> {{ $staff->contact->email }}</p>
            <p><strong>Адрес: </strong> {{ $staff->contact->address }}</p>
            <p><strong>Экстраный кантакт (ФИО): </strong> {{ $staff->contact->emergency_contact_name }}</p>
            <p><strong>Экстраный кантакт (Телефон): </strong> {{ $staff->contact->emergency_contact_phone }}</p>
            <p><strong>Экстраный кантакт (кем приходит): </strong> {{ $staff->contact->emergency_contact_relation }}</p>
        </div>
    </div>

    {{--       {{ $staff->professionalData->admission_period }}  --}}

    <div class="card mt-3">
        <div class="card-header">
            <h5>Проф. данные</h5>
        </div>
        <div class="card-body">
            <p><strong>Должность: </strong> {{ $staff->professionalData->position->name }}</p>
            <p><strong>Дополнительная должность: </strong> {{ $staff->professionalData->additionPosition->name ?? '-' }}</p>
            <p><strong>Специальность: </strong> {{ $staff->professionalData->speciality->name }}</p>
            <p><strong>Категория: </strong> {{ $staff->professionalData->category == 'first' ? "Первая" : "Вторая" }}
            </p>
            <p><strong>Отделения: </strong> {{ $staff->professionalData->department->name }}</p>

            <p>
                <strong>Услуги: </strong> {{ implode(',', $staff->services->pluck('name')->toArray()) }}
            </p>

            <p><strong>Название Образование: </strong> {{ $staff->professionalData->education_name }}</p>
            <p><strong>Дата окончание обучения: </strong> {{ $staff->professionalData->education_graduation_year }}</p>
            <p><strong>Название сертификата: </strong> {{ $staff->professionalData->certificate_name }}</p>
            <p><strong>Номер лицензии: </strong> {{ $staff->professionalData->certificate_number }}</p>
            <p><strong>Дата выдачи: </strong> {{ $staff->professionalData->certificate_issue_date }}</p>
            <p><strong>Срок действия: </strong> {{ $staff->professionalData->certificate_validity_period }}</p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Документы</h5>
        </div>
        <div class="card-body">
            <p><strong>Номер трудового договора: </strong> {{ $staff->document->employment_contract_number }}</p>
            <p><strong>Дата подписания трудового
                    договора: </strong> {{ $staff->document->employment_contract_date_of_signing }}</p>
            <p><strong>Срок действия трудового
                    договора: </strong> {{ $staff->document->employment_contract_validity_period }}</p>
            <p><strong>Дата приказа об
                    увольнении: </strong> {{ $staff->document->dismissal_orders_number }}
            </p>
            <p><strong> Номер акта приема увольнения: </strong> {{ $staff->document->dismissal_orders_date }}</p>

            <p>
                <strong>Номер приказа об
                    увольнении: </strong> {{ $staff->document->dismissal_reception_number }}
            </p>

            <p><strong>Дата увольнения: </strong> {{ $staff->document->dismissal_reception_date }}</p>
            <p><strong>Серия паспорта: </strong> {{ $staff->document->passport_series }}</p>
            <p><strong>ИНН: </strong> {{ $staff->document->INN }}</p>
            <p><strong>СНИЛС: </strong> {{ $staff->document->SNILS }}</p>
        </div>
    </div>
@endsection
