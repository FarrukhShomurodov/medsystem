@extends('admin.layouts.app')
@use('App\Support\Display')

@section('title', $staff->full_name)

@section('header')
    <x-page-header :title="$staff->full_name" :breadcrumbs="['Персонал' => route('staffs.index')]">
        <x-slot:actions>
            @if($isWorking)
                <span class="badge bg-success-lt align-self-center">На работе</span>
            @else
                <span class="badge bg-danger-lt align-self-center">Не работает</span>
            @endif
            @can('edit_staffs')
                @if($staff->admin === null || auth('admin')->user()->can('update', $staff->admin))
                    <a href="{{ route('staffs.edit', $staff) }}" class="btn"><i class="bx bx-edit" aria-hidden="true"></i>Изменить</a>
                @endif
            @endcan
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    @php
        $professional = $staff->professionalData;
        $contact = $staff->contact;
        $document = $staff->document;

        $sections = [
            'Общая информация' => [
                'ФИО' => $staff->full_name,
                'Дата рождения' => Display::date($staff->date_birthday),
                'Пол' => $staff->gender->label(),
                'Гражданство' => $staff->citizenship,
                'Email для входа' => $staff->admin?->email ?? 'нет учётной записи',
            ],
            'Контактная информация' => [
                'Телефон' => $contact?->phone,
                'Email' => $contact?->email,
                'Адрес' => $contact?->address,
                'Экстренный контакт (ФИО)' => $contact?->emergency_contact_name,
                'Экстренный контакт (телефон)' => $contact?->emergency_contact_phone,
                'Экстренный контакт (кем приходится)' => $contact?->emergency_contact_relation,
            ],
            'Проф. данные' => [
                'Должность' => $professional?->position?->name,
                'Дополнительная должность' => $professional?->additionPosition?->name,
                'Специальность' => $professional?->speciality?->name,
                'Категория' => $professional?->category?->label(),
                'Отделение' => $professional?->department?->name,
                'Услуги' => $staff->services->pluck('name')->implode(', '),
                'Учебное заведение' => $professional?->education_name,
                'Дата окончания обучения' => Display::date($professional?->education_graduation_year),
                'Название сертификата' => $professional?->certificate_name,
                'Номер лицензии' => $professional?->certificate_number,
                'Дата выдачи' => Display::date($professional?->certificate_issue_date),
                'Срок действия' => Display::date($professional?->certificate_validity_period),
            ],
            'Документы' => [
                'Номер трудового договора' => $document?->employment_contract_number,
                'Дата подписания трудового договора' => Display::date($document?->employment_contract_date_of_signing),
                'Срок действия трудового договора' => $document?->employment_contract_validity_period,
                'Номер приказа об увольнении' => $document?->dismissal_orders_number,
                'Дата приказа об увольнении' => Display::date($document?->dismissal_orders_date),
                'Номер акта приёма увольнения' => $document?->dismissal_reception_number,
                'Дата увольнения' => Display::date($document?->dismissal_reception_date),
                'Серия паспорта' => $document?->passport_series,
                'ИНН' => $document?->INN,
                'СНИЛС' => $document?->SNILS,
            ],
        ];
    @endphp

    <div class="row row-cards">
        @foreach($sections as $title => $rows)
            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h2 class="card-title">{{ $title }}</h2>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            @foreach($rows as $label => $value)
                                <dt class="col-sm-5 text-secondary fw-normal">{{ $label }}:</dt>
                                <dd class="col-sm-7">{{ filled($value) ? $value : '—' }}</dd>
                            @endforeach
                        </dl>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
