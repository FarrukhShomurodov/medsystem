@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Пациент</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('patients.index') }}">Пациент</a> /</span> {{ $patientDetails['basic_info']['full_name'] }}
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
        <div class="card-header">
            <h5>Общая информация</h5>
        </div>
        <div class="card-body">
            <p><strong>ФИО:</strong> {{ $patientDetails['basic_info']['full_name'] }}</p>
            <p><strong>Дата рождения:</strong> {{ $patientDetails['basic_info']['birth_date'] }}</p>
            <p><strong>Пол:</strong> {{ $patientDetails['basic_info']['gender'] }}</p>
            <p><strong>Гражданство:</strong> {{ $patientDetails['basic_info']['citizenship'] }}</p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Контактная информация</h5>
        </div>
        <div class="card-body">
            <p><strong>Телефон:</strong> {{ $patientDetails['contact_info']['phone'] }}</p>
            <p><strong>Доп. телефон:</strong> {{ $patientDetails['contact_info']['additional_phone'] }}</p>
            <p><strong>Email:</strong> {{ $patientDetails['contact_info']['email'] }}</p>
            <p><strong>Адрес проживания:</strong> {{ $patientDetails['contact_info']['residential_address'] }}</p>
            <p><strong>Адрес регистрации:</strong> {{ $patientDetails['contact_info']['registered_address'] }}</p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Страховая информация</h5>
        </div>
        <div class="card-body">
            <p><strong>Номер полиса:</strong> {{ $patientDetails['insurance_info']['policy_number'] }}</p>
            <p><strong>Компания:</strong> {{ $patientDetails['insurance_info']['company'] }}</p>
            <p><strong>Тип:</strong> {{ $patientDetails['insurance_info']['type'] }}</p>
            <p><strong>Срок действия:</strong> {{ $patientDetails['insurance_info']['expiry_date'] }}</p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Экстренный контакт</h5>
        </div>
        <div class="card-body">
            <p><strong>ФИО:</strong> {{ $patientDetails['emergency_contact']['full_name'] }}</p>
            <p><strong>Отношение:</strong> {{ $patientDetails['emergency_contact']['relation'] }}</p>
            <p><strong>Телефон:</strong> {{ $patientDetails['emergency_contact']['phone'] }}</p>
            <p><strong>Адрес:</strong> {{ $patientDetails['emergency_contact']['address'] }}</p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Медицинская информация</h5>
        </div>
        <div class="card-body">
            <p><strong>Группа крови:</strong> {{ $patientDetails['medical_info']['blood_type'] }}</p>
            <p><strong>Резус фактор:</strong> {{ $patientDetails['medical_info']['rh_factor'] }}</p>
            <p><strong>Особые указания:</strong> {{ $patientDetails['medical_info']['special_instructions'] }}</p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-datatable table-responsive col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-header">История обращение </h5>
                <a href="{{ route('patient.map.create', $patientDetails['patient_id']) }}" class="btn btn-primary "
                   style="margin-right: 22px;">Создать</a>
            </div>
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Даты предыдущих посещений</th>
                    <th>Отделение</th>
                    <th>Врач</th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($patientDetails['history']['maps'] as $map)
                    <tr>
                        <td>{{ $map->id }}</td>
                        <td>{{ $map->admission_date }}</td>
                        <td>{{ $map->department->name }}</td>
                        <td>{{ $map->doctor->name }}</td>

                        <td>
                            <a class="btn btn-sm btn-primary" href="{{ route('map.survey', $map->id) }}"><i
                                    class="bx bx-show me-1"></i> Опросник</a>
                        </td>

                        <td>
                            <a class="btn btn-sm btn-icon"
                               href="{{ route('maps.show', $map->id) }}"><i
                                    class="bx bx-show"></i></a>
                            <a class="btn btn-sm btn-icon"
                               href="{{ route('maps.edit', $map->id) }}"><i
                                    class="bx bx-edit"></i></a>
                            <form action="{{ route('maps.destroy', $map->id) }}" method="POST"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-icon delete-record"><i class="bx bx-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
