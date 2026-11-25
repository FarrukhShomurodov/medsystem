{{-- The warehouse wizard of the create and edit pages. Expects $warehouse (null on create) and $types. --}}
@php
    $contact = $warehouse?->contact;
    $capacity = $warehouse?->capacity;
    $storage = $warehouse?->storageHandling;

    // Every field of a step: name => [label, input type, icon, current value, column, placeholder].
    $contactFields = [
        'phone' => ['Телефон', 'tel', 'bx-phone', $contact?->phone, 'col-md-6 col-xl-4', '+998 (XX) XXX XX XX'],
        'address' => ['Адрес', 'text', 'bx-map', $contact?->address, 'col-md-6 col-xl-4', null],
        'email' => ['Email', 'email', 'bx-envelope', $contact?->email, 'col-md-6 col-xl-4', null],
        'work_time' => ['Время работы', 'text', 'bx-time-five', $contact?->work_time, 'col-md-6 col-xl-4', null],
        'head_name' => ['ФИО руководителя', 'text', 'bx-user', $contact?->head_name, 'col-md-6 col-xl-4', null],
        'head_position' => ['Должность', 'text', 'bx-briefcase', $contact?->head_position, 'col-md-6 col-xl-4', null],
        'head_contact' => ['Контакт', 'text', 'bx-phone', $contact?->head_contact, 'col-12', null],
    ];
    $capacityFields = [
        'total_volume' => ['Общий объём', 'number', 'bx-box', $capacity?->total_volume, 'col-md-6', null],
        'occupied_volume' => ['Занятый объём', 'number', 'bx-package', $capacity?->occupied_volume, 'col-md-6', null],
        'occupied_volume_percentage' => ['Занятый объём в процентах', 'number', 'bx-pie-chart-alt-2', $capacity?->occupied_volume_percentage, 'col-md-6', null],
        'max_capacity' => ['Максимальная вместимость', 'number', 'bx-layer', $capacity?->max_capacity, 'col-md-6', null],
    ];
    $storageFields = [
        'temperature' => ['Температура', 'number', 'bxs-thermometer', $storage?->temperature, 'col-md-6', null],
        'humidity' => ['Влажность', 'number', 'bx-water', $storage?->humidity, 'col-md-6', null],
        'light_protected' => ['Защита от солнечных лучей', 'number', 'bx-sun', $storage?->light_protected, 'col-md-6', null],
    ];
    $locationFields = [
        'refrigeration' => ['Охлаждение', 'number', 'bx-cloud-snow', $storage?->refrigeration, 'col-md-6', null],
        'freezing' => ['Заморозка', 'number', 'bx-cube', $storage?->freezing, 'col-md-6', null],
        'flammable' => ['Легковоспламеняющиеся вещества', 'number', 'bxs-hot', $storage?->flammable, 'col-md-6', null],
        'shelf_location' => ['Расположение на полке', 'number', 'bx-box', $storage?->shelf_location, 'col-md-6', null],
        'special_zone' => ['Специальная зона', 'number', 'bx-map', $storage?->special_zone, 'col-md-6', null],
        'label' => ['Метка', 'number', 'bx-tag', $storage?->label, 'col-md-6', null],
    ];

    $dangerous = filter_var(old('is_dangerous', $storage?->is_dangerous) ?? false, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
@endphp

<div class="bs-stepper wizard-vertical vertical">
    <div class="bs-stepper-header" role="tablist">
        @foreach (['basic-data' => 'Основные данные', 'contacts' => 'Контактная информация', 'capacity' => 'Вместимость', 'storage' => 'Хранение'] as $pane => $label)
            <div class="step" data-target="#{{ $pane }}">
                <button type="button" class="step-trigger" role="tab" id="{{ $pane }}-trigger" aria-controls="{{ $pane }}">
                    <span class="bs-stepper-circle">{{ $loop->iteration }}</span>
                    <span class="bs-stepper-label">{{ $label }}</span>
                </button>
            </div>
        @endforeach
    </div>

    <div class="bs-stepper-content">
        <form method="POST" action="{{ $warehouse ? route('warehouses.update', $warehouse->id) : route('warehouses.store') }}">
            @csrf
            @if ($warehouse)
                @method('PUT')
            @endif

            <div id="basic-data" class="content" role="tabpanel" aria-labelledby="basic-data-trigger">
                <div class="content-header mb-3">
                    <h2 class="h3 mb-0">Основные данные</h2>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required" for="name">Название</label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text"><i class="bx bx-rename" aria-hidden="true"></i></span>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $warehouse?->name) }}">
                        </div>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required" for="warehouse_type_id">Тип</label>
                        <select id="warehouse_type_id" name="warehouse_type_id"
                                class="form-select select2 @error('warehouse_type_id') is-invalid @enderror">
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}" @selected(old('warehouse_type_id', $warehouse?->warehouse_type_id) == $type->id)>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('warehouse_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label required" for="description">Описание</label>
                        <textarea id="description" name="description" rows="3"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $warehouse?->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 d-flex">
                        <button class="btn btn-primary btn-next ms-auto">Далее<i class="bx bx-chevron-right ms-2 me-0" aria-hidden="true"></i></button>
                    </div>
                </div>
            </div>

            @foreach ([
                'contacts' => ['Контактная информация', [$contactFields]],
                'capacity' => ['Вместимость', [$capacityFields]],
                'storage' => ['Хранение', [$storageFields, $locationFields]],
            ] as $pane => [$heading, $groups])
                <div id="{{ $pane }}" class="content" role="tabpanel" aria-labelledby="{{ $pane }}-trigger">
                    <div class="content-header mb-3">
                        <h2 class="h3 mb-0">{{ $heading }}</h2>
                    </div>
                    <div class="row g-3">
                        @foreach ($groups as $fields)
                            @foreach ($fields as $name => [$label, $type, $icon, $value, $column, $placeholder])
                                <div class="{{ $column }}">
                                    <label class="form-label required" for="{{ $name }}">{{ $label }}</label>
                                    <div class="input-group input-group-flat">
                                        <span class="input-group-text"><i class="bx {{ $icon }}" aria-hidden="true"></i></span>
                                        <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
                                               class="form-control @error($name) is-invalid @enderror"
                                               value="{{ old($name, $value) }}" @if ($placeholder) placeholder="{{ $placeholder }}" @endif>
                                    </div>
                                    @error($name)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach

                            @if ($pane === 'storage' && $loop->first)
                                <div class="col-md-6">
                                    <label class="form-label required" for="is_dangerous">Опасные вещества</label>
                                    <select id="is_dangerous" name="is_dangerous"
                                            class="form-select @error('is_dangerous') is-invalid @enderror">
                                        <option value="1" @selected($dangerous === '1')>Имеются</option>
                                        <option value="0" @selected($dangerous === '0')>Не имеются</option>
                                    </select>
                                    @error('is_dangerous')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif
                        @endforeach

                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-prev"><i class="bx bx-chevron-left" aria-hidden="true"></i>Назад</button>
                            @if ($loop->last)
                                <button type="submit" class="btn btn-primary">Сохранить</button>
                            @else
                                <button class="btn btn-primary btn-next">Далее<i class="bx bx-chevron-right ms-2 me-0" aria-hidden="true"></i></button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </form>
    </div>
</div>
