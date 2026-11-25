@extends('admin.layouts.app')
@use('App\Support\Display')

@section('title', $accounting->medication->name)

@section('header')
    <x-page-header :title="$accounting->medication->name"
                   :breadcrumbs="['Медикаменты склада' => auth()->user()?->can('view_warehouse_medications') ? route('warehouses.medications') : null]" />
@endsection

@php
    $packaging = $accounting->packaging;
    $cost = $accounting->medicationCost;
    $supplier = $cost?->supplier;
@endphp

@section('content')
    <div class="row row-cards">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h2 class="card-title">Общая информация</h2>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 fw-medium text-secondary">Склад:</dt>
                        <dd class="col-sm-7">
                            @can('view_warehouses')
                                <a href="{{ route('warehouses.show', $accounting->warehouse->id) }}">{{ $accounting->warehouse->name }}</a>
                            @else
                                {{ $accounting->warehouse->name }}
                            @endcan
                        </dd>
                        <dt class="col-sm-5 fw-medium text-secondary">Препарат:</dt>
                        <dd class="col-sm-7">
                            @can('view_medications')
                                <a href="{{ route('medications.show', $accounting->medication->id) }}">{{ $accounting->medication->name }}</a>
                            @else
                                {{ $accounting->medication->name }}
                            @endcan
                        </dd>
                        <dt class="col-sm-5 fw-medium text-secondary">Кол-во:</dt>
                        <dd class="col-sm-7">{{ $accounting->quantity }}</dd>
                        <dt class="col-sm-5 fw-medium text-secondary">Срок годности:</dt>
                        <dd class="col-sm-7">{{ $accounting->expiration_date?->format('d.m.Y') ?? '—' }}</dd>
                        <dt class="col-sm-5 fw-medium text-secondary">Дата получения:</dt>
                        <dd class="col-sm-7">{{ $accounting->date_of_receipt?->format('d.m.Y') ?? '—' }}</dd>
                        <dt class="col-sm-5 fw-medium text-secondary">Номер партии:</dt>
                        <dd class="col-sm-7">{{ $accounting->batch_number ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h2 class="card-title">Упаковка</h2>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 fw-medium text-secondary">Тип упаковки:</dt>
                        <dd class="col-sm-7">{{ $packaging?->packaging_type?->label() ?? '—' }}</dd>
                        <dt class="col-sm-5 fw-medium text-secondary">Количество в упаковке:</dt>
                        <dd class="col-sm-7">{{ $packaging?->quantity_per_package ?? '—' }}</dd>
                        <dt class="col-sm-5 fw-medium text-secondary">Размер упаковки:</dt>
                        <dd class="col-sm-7">{{ $packaging?->package_size ?? '—' }}</dd>
                        <dt class="col-sm-5 fw-medium text-secondary">Дата упаковки:</dt>
                        <dd class="col-sm-7">{{ Display::date($packaging?->packaging_date) }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Стоимость и поставки</h2>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 fw-medium text-secondary">Розничная цена:</dt>
                        <dd class="col-sm-7">{{ Display::money($cost?->retail_price) }}</dd>
                        <dt class="col-sm-5 fw-medium text-secondary">Закупочная цена:</dt>
                        <dd class="col-sm-7">{{ Display::money($cost?->wholesale_price) }}</dd>
                        <dt class="col-sm-5 fw-medium text-secondary">Поставщик:</dt>
                        <dd class="col-sm-7">{{ collect([$supplier?->name, $supplier?->phone, $supplier?->email])->filter()->implode(', ') ?: '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
