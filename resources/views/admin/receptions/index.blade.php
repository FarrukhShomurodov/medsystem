@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Приемы</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">Приемы</span>
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
            <h5 class="card-header">Приемы</h5>
            <div>
                <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#filtersCollapse">
                    <i class="bx bx-filter-alt"></i> Фильтры
                </button>
            </div>
        </div>

        <div id="filtersCollapse"
             class="collapse @if(request('is_disposable') ||  request('staff_id')) show @endif">
            <div class="card-body">
                <form method="GET" action="{{ route('receptions.index') }}">
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label" for="disposable">Одноразовый</label>
                            <select id="disposable" name="is_disposable"
                                    class="form-control select2">
                                <option value="yes" @selected('yes' == old('is_disposable', request('is_disposable')))>
                                    Да
                                </option>
                                <option value="no" @selected('no' == old('is_disposable', request('is_disposable')))>
                                    Нет
                                </option>
                            </select>
                        </div>

                        @unless(auth('admin')->user()->getRoleNames()->contains('Доктор'))
                            <div class="col-sm-4">
                                <label class="form-label" for="staff">Персонал</label>
                                <select id="staff" name="staff_id" class="form-control select2">
                                    <option value="" selected>Все</option>

                                    @foreach($staffs as $staff)
                                        <option value="{{ $staff->id }}" @selected($staff->id == old('staff_id', request('staff_id')))>
                                            {{ $staff->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endunless

                        <div class="col-sm-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Применить</button>
                            @if(request('is_disposable') || request('staff_id'))
                                <a href="{{ route('receptions.index') }}" class="btn btn-secondary ms-2">Сбросить</a>
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
                    <th>Пациент</th>
                    <th>Персонал</th>
                    <th>Время началы</th>
                    <th>Время конца</th>
                    <th>Дата</th>
                    <th>Коментарий</th>
                </tr>
                </thead>
                <tbody>
                @foreach($receptions as $reception)
                    <tr>
                        <td>{{ $reception->id }}</td>
                        <td>{{ $reception->patient->first_name }}</td>
                        <td>{{ $reception->staff->name }}</td>
                        <td>{{ $reception->start_time }}</td>
                        <td>{{ $reception->end_time }}</td>
                        <td>{{ $reception->date}}</td>
                        <td>{{ $reception->comment}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="mt-4 p-1">
                {{ $receptions->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
