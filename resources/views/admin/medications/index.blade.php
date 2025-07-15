@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Препараты</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">Препараты</span>
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
            <h5 class="card-header">Препараты</h5>
            <div>
                <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#filtersCollapse">
                    <i class="bx bx-filter-alt"></i> Фильтры
                </button>
                <a href="{{ route('medications.create') }}" class="btn btn-primary"
                   style="margin-right: 22px;">Создать</a>
            </div>
        </div>

        <div id="filtersCollapse"
             class="collapse @if(request('active_substance_name') || request('group_id') || request('manufacturer_id')) show @endif">
            <div class="card-body">
                <form method="GET" action="{{ route('medications.index') }}">
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label for="active_substance_name" class="form-label">Активное вещество</label>
                            <input type="text" name="active_substance_name" id="active_substance_name"
                                   class="form-control" value="{{ request('active_substance_name') }}"
                                   placeholder="Введите активное вещество">
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="group">Группы</label>
                            <select id="group" name="group_id"
                                    class="form-control select2"
                                    required>
                                @foreach($groups as $group)
                                    <option
                                        value="{{ $group->id }}" @selected($group->id == old('group_id'))>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="manufacturer">Производители</label>

                            <select id="manufacturer" name="manufacturer_id"
                                    class="form-control select2"
                                    required>
                                @foreach($manufacturers as $manufacturer)
                                    <option
                                        value="{{ $manufacturer->id }}" @selected($manufacturer->id == old('manufacturer_id'))>
                                        {{ $manufacturer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Применить</button>
                            @if(request()->has('active_substance_name') || request('group_id') || request('manufacturer_id'))
                                <a href="{{ route('medications.index') }}" class="btn btn-secondary ms-2">Сбросить</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Название</th>
                    <th>Форма выпуска</th>
                    <th>Дозировка</th>
                    <th>Група</th>
                    <th>Производител</th>
                    <th>Регистрационный номер</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($medications as $medication)
                    <tr class="cursor-pointer">
                        <td onclick="location.href='{{ route('medications.show', $medication->id) }}'">{{ $medication->id }}</td>
                        <td onclick="location.href='{{ route('medications.show', $medication->id) }}'">{{ $medication->name }}</td>
                        <td onclick="location.href='{{ route('medications.show', $medication->id) }}'">{{ $medication->release_form }}</td>
                        <td onclick="location.href='{{ route('medications.show', $medication->id) }}'">{{ $medication->dosage }}</td>
                        <td onclick="location.href='{{ route('medications.show', $medication->id) }}'">{{ $medication->manufacturer->name }}</td>
                        <td onclick="location.href='{{ route('medications.show', $medication->id) }}'">{{ $medication->group->name }}</td>
                        <td onclick="location.href='{{ route('medications.show', $medication->id) }}'">{{ $medication->registration_number }}</td>

                        <td>
                            <div class="d-inline-block text-nowrap">
                                <button class="btn btn-sm btn-warning"
                                        onclick="location.href='{{ route('medications.edit', $medication->id) }}'">
                                    Редактировать
                                </button>
                                <form action="{{ route('medications.destroy', $medication->id) }}" method="POST"
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
            <div class="mt-4 p-1">
                {{ $medications->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
