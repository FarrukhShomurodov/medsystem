@extends('admin.layouts.app')

@section('title', $warehouse->name)

@section('header')
    <x-page-header :title="$warehouse->name" :breadcrumbs="['Склады' => route('warehouses.index')]">
        <x-slot:actions>
            @can('view_warehouse_medications')
                <a href="{{ route('warehouses.medications', ['warehouse_id' => $warehouse->id]) }}" class="btn">
                    <i class="bx bx-capsule" aria-hidden="true"></i>Медикаменты
                </a>
            @endcan
            @can('view_warehouse_supplies')
                <a href="{{ route('warehouses.supplies', ['warehouse_id' => $warehouse->id]) }}" class="btn">
                    <i class="bx bx-package" aria-hidden="true"></i>Расходные материалы
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>
@endsection

@php
    $contact = $warehouse->contact;
    $capacity = $warehouse->capacity;
    $storage = $warehouse->storageHandling;

    $sections = [
        'Основные данные' => [
            'Название:' => $warehouse->name,
            'Тип:' => $warehouse->type?->name,
            'Описание:' => $warehouse->description,
            'Адрес:' => $warehouse->address,
        ],
        'Контактная информация' => [
            'Телефон:' => $contact?->phone,
            'Адрес:' => $contact?->address,
            'Email:' => $contact?->email,
            'Время работы:' => $contact?->work_time,
            'ФИО руководителя:' => $contact?->head_name,
            'Должность:' => $contact?->head_position,
            'Контакт:' => $contact?->head_contact,
        ],
        'Вместимость' => [
            'Общий объём:' => $capacity?->total_volume,
            'Занятый объём:' => $capacity?->occupied_volume,
            'Занятый объём в процентах:' => $capacity?->occupied_volume_percentage,
            'Максимальная вместимость:' => $capacity?->max_capacity,
        ],
        'Хранение' => [
            'Температура:' => $storage?->temperature,
            'Влажность:' => $storage?->humidity,
            'Защита от солнечных лучей:' => $storage?->light_protected,
            'Опасные вещества:' => $storage === null ? null : ($storage->is_dangerous ? 'имеются' : 'не имеются'),
            'Охлаждение:' => $storage?->refrigeration,
            'Заморозка:' => $storage?->freezing,
            'Легковоспламеняющиеся вещества:' => $storage?->flammable,
            'Расположение на полке:' => $storage?->shelf_location,
            'Специальная зона:' => $storage?->special_zone,
            'Метка:' => $storage?->label,
        ],
    ];
@endphp

@section('content')
    <div class="row row-cards">
        @foreach ($sections as $heading => $rows)
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h2 class="card-title">{{ $heading }}</h2>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            @foreach ($rows as $label => $value)
                                <dt class="col-sm-5 fw-medium text-secondary">{{ $label }}</dt>
                                <dd class="col-sm-7">{{ $value ?? '—' }}</dd>
                            @endforeach
                        </dl>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
