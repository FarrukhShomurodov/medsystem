@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Препарат из склада </title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('warehouses.medications') }}">Препараты</a> /</span> {{ $accounting->medication->name }}
    </h6>

    <div class="card">
        <div class="card-header">
            <h5>Общая информация</h5>
        </div>
        <div class="card-body">
            <p><strong>Склад:</strong> <a
                    href="{{ route('warehouses.show', $accounting->warehouse->id ) }}">{{ $accounting->warehouse->name }}</a>
            </p>
            <p><strong>Препарат:</strong> <a
                    href="{{ route('medications.show', $accounting->medication->id ) }}">{{ $accounting->medication->name }}</a>
            </p>
            <p><strong>Кол-во:</strong> {{ $accounting->quantity }}</p>
            <p><strong>Срок годности:</strong> {{ $accounting->expiration_date }}</p>
            <p><strong>Дата получения:</strong> {{ $accounting->date_of_receipt }}</p>
            <p><strong>Номер партии:</strong> {{ $accounting->batch_number }}</p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Упаковка</h5>
        </div>
        <div class="card-body">
            <p><strong>Тип упаковки:</strong> {{ $accounting->packaging->packaging_type }}</p>
            <p><strong>Количество в упаковке:</strong> {{ $accounting->packaging->quantity_per_package }}</p>
            <p><strong>Размер упаковки:</strong> {{ $accounting->packaging->package_size }}</p>
            <p><strong>Дата упаковки:</strong> {{ $accounting->packaging->packaging_date }}</p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Стоимость и поставки</h5>
        </div>
        <div class="card-body">
            <p><strong>Розничная цена:</strong> {{ $accounting->medicationCost->retail_price }}</p>
            <p><strong>Закупочная цена:</strong> {{ $accounting->medicationCost->wholesale_price }}</p>
            <p><strong>Поставщик:</strong> {{ $accounting->medicationCost->supplier->name }}
                , {{ $accounting->medicationCost->supplier->phone }}, {{ $accounting->medicationCost->supplier->email }}
            </p>
        </div>
    </div>

@endsection
