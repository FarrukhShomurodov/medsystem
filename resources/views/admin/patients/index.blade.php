@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Пациенты</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">Пациенты</span>
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

    <div id="errors_alert"></div>

    <div class="card shadow-sm">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Пациенты </h5>
            <div>
                <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#filtersCollapse">
                    <i class="bx bx-filter-alt"></i> Фильтры
                </button>
                <a href="{{ route('patients.create') }}" class="btn btn-primary "
                   style="margin-right: 22px;">Создать</a>
            </div>
        </div>

        <div id="filtersCollapse"
             class="collapse @if(request('department_id') || request('birth_date') || request('staff_id') || request('full_name')  || request('id')) show @endif">
            <div class="card-body">
                <form method="GET" action="{{ route('patients.index') }}">
                    <div class="row g-3">
                        <div class="col-md-2 form-group">
                            <label class="form-label" for="id">ID</label>
                            <div class="input-group input-group-merge">
                                <input type="text" name="id" value="{{ request("id") }}"
                                       id="id"
                                       class="form-control"
                                       placeholder="ID"/>
                            </div>
                        </div>

                        <div class="col-md-2 form-group">
                            <label class="form-label" for="full_name">ФИО</label>
                            <div class="input-group input-group-merge">
                                <input type="text" name="full_name" value="{{ request("full_name") }}"
                                       id="full_name"
                                       class="form-control"
                                       placeholder="ФИО"/>
                            </div>
                        </div>

                        <div class="col-md-2 form-group">
                            <label class="form-label" for="basic-icon-birth-date">Дата рождения </label>
                            <div class="input-group input-group-merge">
                                    <span id="basic-icon-birth-date2" class="input-group-text"><i
                                            class="bx bx-calendar"></i></span>
                                <input type="text" name="birth_date" value="{{ request("birth_date") }}"
                                       id="basic-icon-birth-date"
                                       class="form-control flatpickr-date"
                                       placeholder="YYYY-MM-DD" aria-describedby="basic-icon-birth-date2"/>
                            </div>
                        </div>

                        @php
                            $filters = [
                                ['id' => 'department', 'label' => 'Отделения', 'items' => $departments],
                                ['id' => 'staff', 'label' => 'Врач', 'items' => $staffs],
                            ];
                        @endphp

                        @foreach($filters as $filter)
                            <div class="col-md-2 form-group">
                                <label for="{{ $filter['id'] }}">{{ $filter['label'] }}</label>
                                <select id="{{ $filter['id'] }}" name="{{ $filter['id'] }}_id"
                                        class="form-control select2">
                                    <option value="">Все</option>
                                    @foreach($filter['items'] as $item)
                                        <option value="{{ $item->id }}"
                                            @selected(request("{$filter['id']}_id") == $item->id)>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Применить фильтр</button>
                </form>
            </div>
        </div>

        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Дата поступления</th>
                    <th>Отделения</th>
                    <th>ФИО</th>
                    <th>Дата рождения / Возвраст</th>
                    <th>Палаты</th>
                    <th>Диагноз</th>
                    <th>Дата и название операции</th>
                    <th>Лечащий врач</th>
                    <th>Дней в стационаре</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($patients as $patient)
                    <tr class="pointer-event">
                        <td onclick="location.href='{{ route('patients.show', $patient->id) }}'">{{ $patient->created_at }}</td>
                        <td onclick="location.href='{{ route('patients.show', $patient->id) }}'">
                            {{ $patient->examinationMaps()->first()?->department->name ?? '-' }}
                        </td>
                        <td onclick="location.href='{{ route('patients.show', $patient->id) }}'">
                            {{ $patient->first_name }} {{ $patient->last_name }} {{ $patient->middle_name }}
                        </td>
                        <td onclick="location.href='{{ route('patients.show', $patient->id) }}'">{{ $patient->birth_date }}
                            / {{ \Carbon\Carbon::parse($patient->birth_date)->age }}</td>
                        <td onclick="location.href='{{ route('patients.show', $patient->id) }}'">-</td>
                        <td onclick="location.href='{{ route('patients.show', $patient->id) }}'">{{ $patient->examinationMaps()->first()?->admission_diagnosis_description ?? '-' }}</td>
                        <td onclick="location.href='{{ route('patients.show', $patient->id) }}'">-</td>
                        <td onclick="location.href='{{ route('patients.show', $patient->id) }}'">{{ $patient->examinationMaps()->first()?->doctor->name ?? '-' }}</td>
                        <td onclick="location.href='{{ route('patients.show', $patient->id) }}'">{{ \Carbon\Carbon::parse($patient->examinationMaps()->first()?->admission_date)->age }}</td>

                        <td>
                            <div class="d-inline-block text-nowrap">
                                <button class="btn btn-sm btn-warning"
                                        onclick="location.href='{{ route('patients.edit', $patient->id) }}'">
                                    Редактировать
                                </button>
                            </div>

                            {{--                                <form action="{{ route('patients.destroy', $patient->id) }}" method="POST"--}}
                            {{--                                      style="display:inline;">--}}
                            {{--                                    @csrf--}}
                            {{--                                    @method('DELETE')--}}
                            {{--                                    <button class="btn btn-sm btn-icon delete-record"><i class="bx bx-trash"></i>--}}
                            {{--                                    </button>--}}
                            {{--                                </form>--}}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="mt-4 p-1">
                {{ $patients->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
