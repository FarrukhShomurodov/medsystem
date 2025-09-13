@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Медикамент</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('medications.index') }}">Медикаменты</a> /</span>
        {{ $medication->name }}
    </h6>

    {{-- Алерты --}}
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

    <div class="card shadow-sm">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Информация о медикаменте</h5>

            <div class="d-flex gap-2 me-3">
                <a href="{{ url()->previous() }}" class="btn btn-primary">Назад к списку</a>
            </div>
        </div>

        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                <tr>
                    <th>ID</th>
                    <td>{{ $medication->id }}</td>
                </tr>
                <tr>
                    <th>Название</th>
                    <td>{{ $medication->medication->name }}</td>
                </tr>
                <tr>
                    <th>Производитель</th>
                    <td>{{ $medication->medication->manufacturer?->name ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Форма выпуска</th>
                    <td>{{ $medication->medication->release_form ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Кол-во в упаковке</th>
                    <td>{{ $medication->quantity_per_package ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Размер упаковки</th>
                    <td>{{ $medication->package_size ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Дата упаковки</th>
                    <td>{{ $medication->packaging_date?->format("d.m.Y") ?? "—" }}</td>
                </tr>
                <tr>
                    <th>Розн. цена</th>
                    <td>{{ $medication->retail_price ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Опт. цена</th>
                    <td>{{ $medication->wholesale_price ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Наценка</th>
                    <td>{{ $medication->markup ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Цена вход. итого</th>
                    <td>{{ $medication->incoming_price_total ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Цена вход. за ед.</th>
                    <td>{{ $medication->incoming_price_per_unit ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Сумма входящая</th>
                    <td>{{ $medication->incoming_sum ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Итоговая цена</th>
                    <td>{{ $medication->total_price ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Срок годности</th>
                    <td>{{ $medication->expiration_date?->format("d.m.Y") ?? "—" }}</td>
                </tr>
                <tr>
                    <th>Дата поступления</th>
                    <td>{{ $medication->date_of_receipt ?? "—" }}</td>
                </tr>
                <tr>
                    <th>Партия</th>
                    <td>{{ $medication->batch_number ?? "—" }}</td>
                </tr>
                <tr>
                    <th>Поставщик</th>
                    <td>{{ $medication->supplier?->name ?? "—" }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
