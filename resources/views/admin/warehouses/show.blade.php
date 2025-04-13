@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Склад </title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('warehouses.index') }}">Склад</a> /</span> {{ $warehouse->name }}
    </h6>

    <div class="card">
        <div class="card-header">
            <h5>Основные данные</h5>
        </div>
        <div class="card-body">
            <p><strong>Название:</strong> {{ $warehouse->name }}</p>
            <p><strong>Тип:</strong> {{ $warehouse->description }}</p>
            <p><strong>Описание:</strong> {{ $warehouse->type->name }}</p>
            <p><strong>Медикаменты :</strong> <a
                    href="{{ route('warehouses.medications', ['warehouse_id' => $warehouse->id ]) }}">Смотреть</a></p>
            <p><strong>Расходные материалы :</strong> <a
                    href="{{ route('warehouses.supplies', ['supply_id' => $warehouse->id ]) }}">Смотреть</a></p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Контактная информация</h5>
        </div>
        <div class="card-body">
            <p><strong>Телефон:</strong> {{ $warehouse->contact->phone }}</p>
            <p><strong>Адрес:</strong> {{ $warehouse->contact->address }}</p>
            <p><strong>Email:</strong> {{ $warehouse->contact->phone }}</p>
            <p><strong>Время работы:</strong> {{ $warehouse->contact->work_time }}</p>
            <p><strong>ФИО (руководителя):</strong> {{ $warehouse->contact->head_name }}</p>
            <p><strong>Должность:</strong> {{ $warehouse->contact->head_position }}</p>
            <p><strong>Контакт:</strong> {{ $warehouse->contact->head_contact }}</p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Вместимость</h5>
        </div>
        <div class="card-body">
            <p><strong>Общий объем:</strong> {{ $warehouse->capacity->total_volume }}</p>
            <p><strong>Занятый объем:</strong> {{ $warehouse->capacity->occupied_volume }}</p>
            <p><strong>Занятый объем в процентах:</strong> {{ $warehouse->capacity->occupied_volume_percentage }}</p>
            <p><strong>Максимальная вместимость :</strong> {{ $warehouse->capacity->max_capacity }}</p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Хранение</h5>
        </div>
        <div class="card-body">
            <p><strong>Температура:</strong> {{ $warehouse->storageHandling->temperature }}</p>
            <p><strong>Влажность:</strong> {{ $warehouse->storageHandling->humidity }}</p>
            <p><strong>Защита от солнечных лучей:</strong> {{ $warehouse->storageHandling->light_protected }}</p>
            <p><strong>Опасные
                    вешества:</strong> {{ $warehouse->storageHandling->is_dangerous ? "имеется" : "не имеется"}}</p>
            <p><strong>Охлаждение:</strong> {{ $warehouse->storageHandling->refrigeration }}</p>
            <p><strong>Заморозка лучей:</strong> {{ $warehouse->storageHandling->freezing }}</p>
            <p><strong>Легковоспламеняющийся лучей:</strong> {{ $warehouse->storageHandling->flammable }}</p>
            <p><strong>Расположение на полке:</strong> {{ $warehouse->storageHandling->shelf_location }}</p>
            <p><strong>Специальная зона:</strong> {{ $warehouse->storageHandling->special_zone }}</p>
            <p><strong>Метка:</strong> {{ $warehouse->storageHandling->label }}</p>
        </div>
    </div>
@endsection
