@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Медикаменты склада</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">Медикаменты склада</span>
    </h6>

    <div class="card shadow-sm">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Медикаменты склада </h5>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Склад</th>
                    <th>Материал</th>
                    <th>Кол-во</th>
                </tr>
                </thead>
                <tbody>
                @foreach($supplies as $supply)
                    @foreach($supply->warehouse as $warehouse)
                        <tr>
                            <td>{{ $supply->id }}</td>
                            <td>
                                <a class="text-primary"
                                   href="{{ route('warehouses.show', $warehouse->id) }}">{{ $warehouse->name }}</a>
                            </td>
                            <td>{{ $supply->name }}</td>
                            <td>{{ $warehouse->pivot->quantity }}</td>
                        </tr>
                @endforeach
                @endforeach
            </table>
            <div class="mt-4 p-1">
                {{ $supplies->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
