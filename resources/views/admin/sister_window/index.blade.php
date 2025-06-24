@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Сестринское окно</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">Сестринское окно</span>
    </h6>

    <div class="card shadow-sm">
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
                @foreach($patients as $patient)
                    <tr class="patient-row cursor-pointer"
                        onclick="window.location.href='{{ route('sister.window.show',$patient->id) }}'">
                        <td>{{ $patient->first_name }} {{ $patient->last_name }} {{ $patient->middle_name }}
                            ID{{ $patient->id }}</td>
                        <td>-</td>
                        <td>{{ \Carbon\Carbon::parse($patient->birth_date)->age }} лет</td>
                        <td>-</td>
                        <td>-</td>
                        <td>{{ \Carbon\Carbon::parse($patient->admission_date)->age }}</td>
                        <td>-</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
