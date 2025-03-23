@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Склад</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('warehouses.index') }}">Склад </a> /</span>Создать
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
    </div>

    <div class="bs-stepper wizard-numbered wizard-vertical vertical shadow-sm mt-2">
        <div class="bs-stepper-header">
            <div class="step" data-target="#basic-data">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">1</span>
                    <span class="bs-stepper-label">Основные данные</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#contacts">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">2</span>
                    <span class="bs-stepper-label">Контактная информация</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#employee_details">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">3</span>
                    <span class="bs-stepper-label">Вместимость</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#licenses">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">4</span>
                    <span class="bs-stepper-label">Инвентаризация</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#resources">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">5</span>
                    <span class="bs-stepper-label">Хранение</span>
                </button>
            </div>
        </div>

        <div class="bs-stepper-content">
            <form method="post" action="{{ route('warehouses.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- Основные данные -->
                <div id="basic-data" class="content">

                    <div class="content-header mb-3">
                        <h6 class="mb-0">Основные данные</h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-company">Название <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('name') text-danger icon-border @enderror"><i
                                    class="bx bx-rename"></i></span>
                                <input type="text" id="basic-icon-default-company"
                                       class="form-control @error('name') is-invalid @enderror"
                                       placeholder="Название" aria-label="Название"
                                       name="name"
                                       value="{{ old('name') }}"
                                       aria-describedby="basic-icon-default-company2"/>
                            </div>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="type">Типы <span
                                    class="text-danger">*</span></label>
                            <select id="type" name="warehouse_type_id"
                                    class="form-control select2 @error('warehouse_type_id') is-invalid @enderror">
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}" @selected(old('warehouse_type_id') == $type->id)>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('warehouse_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="basic-icon-default-description">Описание </label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-history2"
                                      class="input-group-text @error('description') text-danger icon-border @enderror">
                                    <i class="bx bx-notepad"></i>
                                </span>
                                <textarea id="basic-icon-default-description"
                                          class="form-control @error('description') is-invalid @enderror"
                                          placeholder="Описание"
                                          aria-label="Описание"
                                          name="description"
                                          aria-describedby="basic-icon-default-history2">{{ old('description') }}</textarea>
                            </div>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Navigation -->
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-label-secondary btn-prev" disabled>
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i> Назад
                            </button>
                            <button class="btn btn-primary btn-next">
                                Далее <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Контактная информация -->
                <div id="contacts" class="content">

                    <div class="content-header mb-3">
                        <h6 class="mb-0">Контактная информация</h6>
                    </div>
                    <div class="row g-3">

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-phone">Телефон </label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-phone2"
                                  class="input-group-text @error('phone') text-danger icon-border @enderror"><i
                                    class="bx bx-phone"></i></span>
                                <input type="text" id="basic-icon-default-phone"
                                       class="form-control phone @error('phone') is-invalid @enderror"
                                       name="phone"
                                       value="{{ old('phone') }}"
                                       placeholder="+ 998 (XX) XXX XX XX" aria-label="+ 998 (XX) XXX XX XX"
                                       aria-describedby="basic-icon-default-phone2"/>
                            </div>

                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-address">
                                Адрес
                            </label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('address') text-danger icon-border @enderror">
                                <i class="bx bx-map"></i>
                            </span>
                                <input type="text" id="basic-icon-default-address"
                                       class="form-control @error('address') is-invalid @enderror"
                                       placeholder="Адрес" aria-label="Адрес"
                                       name="address"
                                       value="{{ old('address') }}"
                                       aria-describedby="basic-icon-default-company2"/>
                            </div>

                            @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-email">Email</label>
                            <div class="input-group input-group-merge">
                            <span class="input-group-text @error('email') text-danger icon-border @enderror"><i
                                    class="bx bx-envelope"></i></span>
                                <input type="email" id="basic-icon-default-email"
                                       class="form-control @error('email') is-invalid @enderror" placeholder="Email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       aria-label="Email" aria-describedby="basic-icon-default-email2"/>
                            </div>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-work_time">Время работы</label>
                            <div class="input-group input-group-merge">
                            <span class="input-group-text @error('work_time') text-danger icon-border @enderror"><i
                                    class="bx bx-printer"></i></span>
                                <input type="text" id="basic-icon-default-work_time"
                                       class="form-control @error('work_time') is-invalid @enderror"
                                       placeholder="Время работы"
                                       name="work_time"
                                       value="{{ old('work_time') }}"
                                       aria-label="Время работы" aria-describedby="basic-icon-default-fax2"/>
                            </div>
                            @error('work_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-head_name">ФИО (руководителя)</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text @error('head_name') text-danger icon-border @enderror"><i
                                        class="bx bx-user"></i></span>
                                <input type="text" id="basic-icon-default-head_name"
                                       class="form-control @error('head_name') is-invalid @enderror"
                                       placeholder="ФИО (руководителя)"
                                       name="head_name"
                                       value="{{ old('head_name') }}"
                                       aria-label="ФИО (руководителя)" aria-describedby="basic-icon-default-head_name"/>
                            </div>
                            @error('head_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-head_position">Должность</label>
                            <div class="input-group input-group-merge">
                            <span class="input-group-text @error('head_position') text-danger icon-border @enderror"><i
                                    class="bx bx-briefcase"></i></span>
                                <input type="text" id="basic-icon-default-head_position"
                                       class="form-control @error('head_position') is-invalid @enderror"
                                       placeholder="Должность"
                                       name="head_position"
                                       value="{{ old('head_position') }}"
                                       aria-label="Должность" aria-describedby="basic-icon-default-head_position"/>
                            </div>
                            @error('head_position')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="basic-icon-default-head_contact">Контакт</label>
                            <div class="input-group input-group-merge">
                            <span class="input-group-text @error('head_contact') text-danger icon-border @enderror">
                                <i class="bx bx-phone"></i>
                            </span>
                                <input type="text" id="basic-icon-default-head_contact"
                                       class="form-control @error('head_contact') is-invalid @enderror"
                                       placeholder="Контакт"
                                       name="head_contact"
                                       value="{{ old('head_contact') }}"
                                       aria-label="Контакт" aria-describedby="basic-icon-default-head_contact"/>
                            </div>
                            @error('head_contact')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Navigation -->
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev">
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i> Назад
                            </button>
                            <button class="btn btn-primary btn-next">
                                Далее <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Вместимость -->
                <div id="employee_details" class="content">

                    <div class="content-header mb-3">
                        <h6 class="mb-0">Вместимость</h6>
                    </div>
                    <div class="row g-3">
                        <!-- total_volume -->
                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-total_volume">Общий объем</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-total_volume"
                                      class="input-group-text @error('total_volume') text-danger icon-border @enderror">
                                    <i class="bx bx-box"></i>
                                </span>
                                <input type="number" id="basic-icon-default-total_volume"
                                       class="form-control @error('total_volume') is-invalid @enderror"
                                       name="total_volume"
                                       value="{{ old('total_volume') }}"
                                       placeholder="Общий объем" aria-label="Общий объем"
                                       aria-describedby="basic-icon-default-total_volume"/>
                            </div>
                            @error('total_volume')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- occupied_volume -->
                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-occupied_volume">Занятый объем</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-occupied_volume"
                                      class="input-group-text @error('occupied_volume') text-danger icon-border @enderror">
                                    <i class="bx bx-package"></i>
                                </span>
                                <input type="number" id="basic-icon-default-occupied_volume"
                                       class="form-control @error('occupied_volume') is-invalid @enderror"
                                       name="occupied_volume"
                                       value="{{ old('occupied_volume') }}"
                                       placeholder="Занятый объем" aria-label="Занятый объем"
                                       aria-describedby="basic-icon-default-occupied_volume"/>
                            </div>
                            @error('occupied_volume')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- occupied_volume_percentage -->
                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-occupied_volume_percentage">Занятый объем в процентах</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-occupied_volume_percentage"
                                      class="input-group-text @error('occupied_volume_percentage') text-danger icon-border @enderror">
                                    <i class="bx bx-pie-chart-alt-2"></i>
                                </span>
                                <input type="number" id="basic-icon-default-occupied_volume_percentage"
                                       class="form-control @error('occupied_volume_percentage') is-invalid @enderror"
                                       name="occupied_volume_percentage"
                                       value="{{ old('occupied_volume_percentage') }}"
                                       placeholder="Занятый объем в процентах" aria-label="Занятый объем в процентах"
                                       aria-describedby="basic-icon-default-occupied_volume_percentage"/>
                            </div>
                            @error('occupied_volume_percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- max_capacity -->
                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-max_capacity">Максимальная вместимость</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-max_capacity"
                                      class="input-group-text @error('max_capacity') text-danger icon-border @enderror">
                                    <i class="bx bx-layer"></i>
                                </span>
                                <input type="number" id="basic-icon-default-max_capacity"
                                       class="form-control @error('max_capacity') is-invalid @enderror"
                                       name="max_capacity"
                                       value="{{ old('max_capacity') }}"
                                       placeholder="Максимальная вместимость" aria-label="Максимальная вместимость"
                                       aria-describedby="basic-icon-default-max_capacity"/>
                            </div>
                            @error('max_capacity')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Navigation -->
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev">
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i> Назад
                            </button>
                            <button class="btn btn-primary btn-next">
                                Далее <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Инвентаризация -->
                <div id="licenses" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Инвентаризация</h6>
                    </div>
                    <div class="row g-3">
                        <!-- Медикаменты -->
                        <div class="col-sm-12">
                            <label class="form-label" for="medication">Медикаменты <span
                                    class="text-danger">*</span></label>
                            <select id="medication" name=""
                                    class="form-control select2 @error('medication') is-invalid @enderror"
                                    multiple="multiple">
                                @foreach($medications as $medication)
                                    <option
                                        value="{{ $medication->id }}" @selected(in_array($medication->id, old('medications',[])))>{{ $medication->name }}</option>
                                @endforeach
                            </select>
                            @error('medication')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <!-- Контейнер для динамических полей -->
                        <div id="medication-details-container">
                        </div>

                        <!-- Шаблон для динамических полей (скрыт) -->
                        <div id="medication-details-template" style="display: none;">
                            <div class="medication-details">
                                <h5>Медикамент: <span class="medication-name"></span></h5>
                                <input type="hidden" name="medications[ID][id]" value="ID">

                                <div class="form-group mb-3">
                                    <label for="quantity_ID">Количество <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-plus"></i></span>
                                        <input type="number" id="quantity_ID" name="medications[ID][quantity]"
                                               class="form-control" placeholder="Введите количество">
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="expiration_date_ID">Срок годности <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                        <input type="date" id="expiration_date_ID"
                                               name="medications[ID][expiration_date]"
                                               placeholder="YYYY-MM-DD"
                                               class="form-control flatpickr-date">
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="date_of_receipt_ID">Дата получения <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-calendar-event"></i></span>
                                        <input type="date" id="date_of_receipt_ID"
                                               name="medications[ID][date_of_receipt]"
                                               placeholder="YYYY-MM-DD"
                                               class="form-control flatpickr-date">
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="batch_number_ID">Номер партии <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-barcode"></i></span>
                                        <input type="text" id="batch_number_ID" name="medications[ID][batch_number]"
                                               class="form-control" placeholder="Введите номер партии">
                                    </div>
                                </div>

                                <h6>Упаковка:</h6>

                                <div class="form-group mb-3">
                                    <label for="packaging_type_ID">Тип упаковки <span
                                            class="text-danger">*</span></label>
                                    <select id="packaging_type_ID" name="medications[ID][packaging_type]"
                                            class="form-control select2type" required>
                                        @foreach(\App\Models\MedicationPackaging::TYPE as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="quantity_per_package_ID">Количество в упаковке <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-box"></i></span>
                                        <input type="number" id="quantity_per_package_ID"
                                               name="medications[ID][quantity_per_package]" class="form-control"
                                               placeholder="Введите количество в упаковке">
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="package_size_ID">Размер упаковки <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-expand"></i></span>
                                        <input type="number" id="package_size_ID" name="medications[ID][package_size]"
                                               placeholder="Введите размер упаковки"
                                               class="form-control">
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="packaging_date_ID">Дата упаковки <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-calendar-check"></i></span>
                                        <input type="text" id="packaging_date_ID" name="medications[ID][packaging_date]"
                                               placeholder="YYYY-MM-DD"
                                               class="form-control flatpickr-date" required>
                                    </div>
                                </div>

                                <h6>Стоимость и поставки</h6>

                                <div class="form-group mb-3">
                                    <label for="retail_price_ID">Розничная цена </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-money"></i></span>
                                        <input type="number" id="retail_price_ID" name="medications[ID][retail_price]"
                                               class="form-control" placeholder="Розничная цена">
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="wholesale_price_ID">Закупочная цена </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-wallet"></i></span>
                                        <input type="number" id="wholesale_price_ID"
                                               name="medications[ID][wholesale_price]" class="form-control"
                                               placeholder="Закупочная цена">
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="supplier_ID">Поставщик (должен совпадать с производителем препарата)
                                        <span class="text-danger">*</span></label>
                                    <select id="supplier_ID" name="medications[ID][supplier_id]"
                                            class="form-control select2type" required>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Навигация -->
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev">
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i> Назад
                            </button>
                            <button class="btn btn-primary btn-next">
                                Далее <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Хранение -->
                <div id="resources" class="content">

                    <div class="content-header mb-3">
                        <h6 class="mb-0">Хранение</h6>
                    </div>
                    <div class="row g-3">

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-temperature">Температура </label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-temperature"
                                  class="input-group-text @error('temperature') text-danger icon-border @enderror"><i
                                    class="bx bxs-thermometer"></i></span>
                                <input type="number" id="basic-icon-default-temperature"
                                       class="form-control @error('temperature') is-invalid @enderror"
                                       name="temperature"
                                       value="{{ old('temperature') }}"
                                       placeholder="Температура" aria-label="Температура"
                                       aria-describedby="basic-icon-default-temperature"/>
                            </div>

                            @error('temperature')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-humidity">Влажность </label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-humidity"
                                  class="input-group-text @error('humidity') text-danger icon-border @enderror"><i
                                    class="bx bx-water"></i></span>
                                <input type="number" id="basic-icon-default-humidity"
                                       class="form-control @error('humidity') is-invalid @enderror"
                                       name="humidity"
                                       value="{{ old('humidity') }}"
                                       placeholder="Влажность" aria-label="Влажность"
                                       aria-describedby="basic-icon-default-humidity"/>
                            </div>

                            @error('humidity')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-light_protected">Защита от солнечных
                                лучей </label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-light_protected"
                                  class="input-group-text @error('light_protected') text-danger icon-border @enderror"><i
                                    class="bx bx-sun"></i></span>
                                <input type="number" id="basic-icon-default-light_protected"
                                       class="form-control @error('light_protected') is-invalid @enderror"
                                       name="light_protected"
                                       value="{{ old('light_protected') }}"
                                       placeholder="Защита от солнечных лучей"
                                       aria-label="Защита от солнечных лучей"
                                       aria-describedby="basic-icon-default-light_protected"/>
                            </div>

                            @error('light_protected')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="is_dangerous">Опасные вешества <span
                                    class="text-danger">*</span></label>
                            <select id="is_dangerous" name="is_dangerous"
                                    class="form-control select2 @error('is_dangerous') is-invalid @enderror">
                                <option value="1" @selected(old('is_dangerous') == 1)>
                                    Имеется
                                </option>
                                <option value="0" @selected(old('is_dangerous') == 0)>
                                    Не имеется
                                </option>
                            </select>

                            @error('is_dangerous')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-refrigeration">Охлаждение</label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-refrigeration"
                                  class="input-group-text @error('refrigeration') text-danger icon-border @enderror">
                                <i class='bx bx-cloud-snow'></i>
                            </span>
                                <input type="number" id="basic-icon-default-refrigeration"
                                       class="form-control @error('refrigeration') is-invalid @enderror"
                                       name="refrigeration"
                                       value="{{ old('refrigeration') }}"
                                       placeholder="Охлаждение" aria-label="Охлаждение"
                                       aria-describedby="basic-icon-default-refrigeration"/>
                            </div>

                            @error('refrigeration')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-freezing">Заморозка
                                лучей </label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-freezing"
                                  class="input-group-text @error('freezing') text-danger icon-border @enderror"><i
                                    class="bx bx-bed"></i></span>
                                <input type="number" id="basic-icon-default-freezing"
                                       class="form-control @error('freezing') is-invalid @enderror"
                                       name="freezing"
                                       value="{{ old('freezing') }}"
                                       placeholder="Заморозка" aria-label="Заморозка"
                                       aria-describedby="basic-icon-default-freezing"/>
                            </div>

                            @error('freezing')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-flammable">Легковоспламеняющийся
                                лучей </label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-flammable"
                                  class="input-group-text @error('flammable') text-danger icon-border @enderror"><i
                                    class="bx bxs-hot"></i></span>
                                <input type="number" id="basic-icon-default-flammable"
                                       class="form-control @error('flammable') is-invalid @enderror"
                                       name="flammable"
                                       value="{{ old('flammable') }}"
                                       placeholder="Легковоспламеняющийся" aria-label="Легковоспламеняющийся"
                                       aria-describedby="basic-icon-default-flammable"/>
                            </div>

                            @error('flammable')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-shelf_location">Расположение на полке</label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-shelf_location"
                                  class="input-group-text @error('shelf_location') text-danger icon-border @enderror"><i
                                    class="bx bx-box"></i></span>
                                <input type="number" id="basic-icon-default-shelf_location"
                                       class="form-control @error('shelf_location') is-invalid @enderror"
                                       name="shelf_location"
                                       value="{{ old('shelf_location') }}"
                                       placeholder="Расположение на полке" aria-label="расположение на полке"
                                       aria-describedby="basic-icon-default-shelf_location"/>
                            </div>

                            @error('shelf_location')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-special_zone">Специальная
                                зона </label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-special_zone"
                                  class="input-group-text @error('special_zone') text-danger icon-border @enderror"><i
                                    class="bx bx-map"></i></span>
                                <input type="number" id="basic-icon-default-special_zone"
                                       class="form-control @error('special_zone') is-invalid @enderror"
                                       name="special_zone"
                                       value="{{ old('special_zone') }}"
                                       placeholder="Специальная зона" aria-label="Специальная зона"
                                       aria-describedby="basic-icon-default-special_zone"/>
                            </div>

                            @error('special_zone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-label">Метка </label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-label"
                                  class="input-group-text @error('label') text-danger icon-border @enderror"><i
                                    class="bx bx-tag"></i></span>
                                <input type="number" id="basic-icon-default-label"
                                       class="form-control @error('label') is-invalid @enderror"
                                       name="label"
                                       value="{{ old('label') }}"
                                       placeholder="Специальная зона" aria-label="Метка"
                                       aria-describedby="basic-icon-default-label"/>
                            </div>

                            @error('label')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <!-- Navigation -->
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev">
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i> Назад
                            </button>
                            <button type="submit" class="btn btn-success">Сохранить</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            // Удаление медикамента при снятии выбора
            $('.select2').on('select2:unselect', function (e) {
                let medicationId = e.params.data.id;
                $(`#medication-details-container .medication-details[data-id="${medicationId}"]`).remove();
            });

            // Добавление медикамента
            $('#medication').on('change', function () {
                const container = $('#medication-details-container');
                const template = $('#medication-details-template').html();

                $('#medication option:selected').each(function () {
                    const option = $(this);
                    const medicationId = option.val();
                    const medicationName = option.text();

                    // Проверяем, существует ли уже этот медикамент в контейнере
                    if (container.find(`.medication-details[data-id="${medicationId}"]`).length === 0) {
                        const newFields = $(template.replace(/ID/g, medicationId)).css('display', 'block');

                        newFields.attr('data-id', medicationId); // Добавляем атрибут data-id для удобного поиска
                        newFields.find('.medication-name').text(medicationName);
                        container.append(newFields);

                        // Корректно инициализируем Select2 и flatpickr
                        newFields.find('.select2type').select2();
                        newFields.find('.flatpickr-date').flatpickr({ dateFormat: 'Y-m-d' });
                    }
                });
            });
        });
    </script>
@endsection
