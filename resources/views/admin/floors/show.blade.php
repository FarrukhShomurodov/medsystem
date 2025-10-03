@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Этаж - {{ $floor->name }}</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">
            <a href="{{ route('floors.index') }}">Этажи</a> /
        </span>
        {{ $floor->name }}
    </h6>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Информация об этаже</h5>
        </div>
        <div class="card-body">
            <p><strong>Название:</strong> {{ $floor->name }}</p>
        </div>
    </div>

    {{-- Отделения --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Отделения</h5>
            <a href="{{ route('departments.create') }}" class="btn btn-sm btn-primary">Добавить отделение</a>
        </div>
        <div class="card-body">
            @if($floor->departments->count())
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Название</th>
                        <th>Склад</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($floor->departments as $index => $department)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $department->name }}</td>
                            <td>{{ $department->warehouse->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">Нет отделений на этом этаже.</p>
            @endif
        </div>
    </div>

    {{-- Кабинеты --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Кабинеты</h5>
            <a href="{{ route('offices.create') }}" class="btn btn-sm btn-primary">Добавить кабинет</a>
        </div>
        <div class="card-body">
            @if($floor->offices->count())
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Название</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($floor->offices as $index => $office)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $office->name }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">Нет кабинетов на этом этаже.</p>
            @endif
        </div>
    </div>
@endsection
