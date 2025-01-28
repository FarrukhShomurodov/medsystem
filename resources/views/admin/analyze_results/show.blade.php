@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Результаты анализа</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('analyzes.index') }}">Анализы</a> /</span>Результаты анализа
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
            <h5 class="card-header">Результаты анализа </h5>
            <a href="{{ route('analyze.result.edit', $results->first()->analyze_id) }}" class="btn btn-warning "
               style="margin-right: 22px;">Редактировать</a>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Наименование показателя</th>
                    <th>Единицы измерения</th>
                    <th>Значение</th>
                    <th>Референсные значения</th>
                    <th>Комментарии</th>
                </tr>
                </thead>
                <tbody>
                @foreach($results as $result)
                    <tr>
                        <td>{{ $result->id }}</td>
                        <td>{{ $result->type->name }}</td>
                        <td>{{ $result->unit }}</td>
                        <td>{{ $result->value }}</td>
                        <td>{{ $result->reference_range }}</td>
                        <td>{{ $result->comment }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
