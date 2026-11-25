@extends('admin.layouts.app')
@use('App\Support\Display')

@php
    $medication = $batch->medication;
    $pageTitle = "{$medication->name}, партия {$batch->batch_number}";

    $rows = [
        'ID партии' => $batch->id,
        'Название' => $medication->name,
        'Производитель' => $medication->manufacturer?->name,
        'Форма выпуска' => $medication->release_form,
        'Остаток' => $batch->quantity,
        'Тип упаковки' => $batch->packaging_type?->label(),
        'Кол-во в упаковке' => $batch->quantity_per_package,
        'Размер упаковки' => $batch->package_size,
        'Дата упаковки' => $batch->packaging_date?->format('d.m.Y'),
        'Розн. цена' => Display::money($batch->retail_price),
        'Опт. цена' => Display::money($batch->wholesale_price),
        'Наценка' => Display::percent($batch->markup),
        'Цена вход. итого' => Display::money($batch->incoming_price_total),
        'Цена вход. за ед.' => Display::unitPrice($batch->incoming_price_per_unit),
        'Сумма входящая' => Display::money($batch->incoming_sum),
        'Итоговая цена' => Display::money($batch->total_price),
        'Срок годности' => $batch->expiration_date?->format('d.m.Y'),
        'Дата поступления' => $batch->date_of_receipt?->format('d.m.Y'),
        'Партия' => $batch->batch_number,
        'Поставщик' => $batch->supplier?->name,
    ];
@endphp

@section('title', $pageTitle)

@section('header')
    <x-page-header :title="$pageTitle" :breadcrumbs="['Остатки общего склада' => route('main.warehouse.remains')]">
        <x-slot:actions>
            <a href="{{ route('main.warehouse.remains') }}" class="btn"><i class="bx bx-arrow-back" aria-hidden="true"></i>Назад к остаткам</a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Партия медикамента</h2>
        </div>
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <tbody>
                @foreach ($rows as $label => $value)
                    <tr>
                        <th scope="row" class="w-25 fw-medium text-secondary">{{ $label }}</th>
                        <td>{{ $value ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
