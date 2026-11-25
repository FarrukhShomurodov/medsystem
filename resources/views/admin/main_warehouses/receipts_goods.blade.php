@extends('admin.layouts.app')

@php
    $runningOutCount = count($remainMedications) + count($remainSupplies) + count($remainHousehold);

    // The three parts of a receipt: the items picked in the select get a line each (resources/js/pages/main-warehouse-receipts.js).
    $sections = [
        'medications' => [
            'title' => 'Медикаменты', 'icon' => 'bx-capsule', 'colour' => 'primary', 'select' => 'medication',
            'label' => 'Выберите медикаменты', 'items' => $medications,
            'empty' => 'Выберите медикаменты из списка выше, чтобы добавить их на склад',
        ],
        'supplies' => [
            'title' => 'Расходные материалы', 'icon' => 'bx-injection', 'colour' => 'success', 'select' => 'supply',
            'label' => 'Выберите расходные материалы', 'items' => $supplies,
            'empty' => 'Выберите материалы из списка выше, чтобы добавить их на склад',
        ],
        'household' => [
            'title' => 'Хозяйственные материалы', 'icon' => 'bx-brush', 'colour' => 'warning', 'select' => 'household',
            'label' => 'Выберите хозяйственные материалы', 'items' => $householdMaterial,
            'empty' => 'Выберите материалы из списка выше, чтобы добавить их на склад',
        ],
    ];

    // The fields of a medication line: name => [label, required, icon, input attributes].
    $batchFields = [
        'quantity' => ['Количество', true, 'bx-calculator', ['type' => 'number']],
        'expiration_date' => ['Срок годности', true, 'bx-calendar', ['type' => 'text', 'class' => 'flatpickr-date', 'placeholder' => 'ГГГГ-ММ-ДД', 'data-min-date' => today()->addDay()->toDateString()]],
        'date_of_receipt' => ['Дата получения', true, 'bxs-truck', ['type' => 'text', 'class' => 'flatpickr-date', 'placeholder' => 'ГГГГ-ММ-ДД', 'data-max-date' => 'today']],
        'batch_number' => ['Номер партии', true, 'bx-barcode', ['type' => 'text']],
    ];
    $packageFields = [
        'quantity_per_package' => ['Кол-во в упаковке', false, 'bx-box', ['type' => 'number']],
        'package_size' => ['Размер упаковки', false, 'bx-ruler', ['type' => 'number']],
    ];
    $priceFields = [
        'retail_price' => ['Розничная цена', true, 'bx-tag', ['type' => 'number', 'step' => '0.01']],
        'wholesale_price' => ['Закупочная цена', true, 'bx-receipt', ['type' => 'number', 'step' => '0.01']],
    ];
    $extraFields = [
        'markup' => ['Наценка (%)', false, null, ['type' => 'number', 'step' => '0.01']],
        'total_price' => ['Сумма', false, null, ['type' => 'number', 'step' => '0.01']],
        'incoming_price_total' => ['Цена вх. (всего)', false, null, ['type' => 'number', 'step' => '0.01']],
        'incoming_price_per_unit' => ['Цена вх. за ед.', false, null, ['type' => 'number', 'step' => '0.0001']],
        'incoming_sum' => ['Сумма вх.', false, null, ['type' => 'number', 'step' => '0.01']],
    ];
@endphp

@section('title', 'Приход на общий склад')

@section('header')
    <x-page-header title="Приход товаров" :breadcrumbs="['Остатки общего склада' => route('main.warehouse.remains')]">
        <x-slot:actions>
            <button type="submit" form="receipt-form" class="btn btn-primary"><i class="bx bx-save" aria-hidden="true"></i>Сохранить приход</button>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    <div class="accordion mb-3" id="running-out">
        <div class="accordion-item">
            <h2 class="accordion-header" id="running-out-heading">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#running-out-list"
                        aria-expanded="false" aria-controls="running-out-list">
                    Заканчивающиеся позиции
                    <span class="badge bg-warning-lt ms-2">{{ $runningOutCount }}</span>
                </button>
            </h2>
            <div id="running-out-list" class="accordion-collapse collapse" aria-labelledby="running-out-heading">
                <div class="accordion-body p-0">
                    <div class="table-responsive">
                        <table class="table table-vcenter mb-0">
                            <thead>
                            <tr>
                                <th class="w-1">#</th>
                                <th>Тип</th>
                                <th>Название</th>
                                <th class="text-end">Остаток</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php $index = 1; @endphp

                            {{-- One row per drug: its units in date over all its batches --}}
                            @foreach ($remainMedications as $line)
                                <tr>
                                    <td class="text-secondary">{{ $index++ }}</td>
                                    <td><span class="badge bg-info-lt">Медикамент</span></td>
                                    <td>{{ $line['medication']->name }}</td>
                                    <td class="text-end">{{ $line['onHand'] }}</td>
                                </tr>
                            @endforeach

                            @foreach ($remainSupplies as $row)
                                <tr>
                                    <td class="text-secondary">{{ $index++ }}</td>
                                    <td><span class="badge bg-success-lt">Расходник</span></td>
                                    <td>{{ $row->supply->name }}</td>
                                    <td class="text-end">{{ $row->quantity }}</td>
                                </tr>
                            @endforeach

                            @foreach ($remainHousehold as $row)
                                <tr>
                                    <td class="text-secondary">{{ $index++ }}</td>
                                    <td><span class="badge bg-warning-lt">Хоз. материал</span></td>
                                    <td>{{ $row->material->name }}</td>
                                    <td class="text-end">{{ $row->quantity }}</td>
                                </tr>
                            @endforeach

                            @if ($runningOutCount === 0)
                                <tr>
                                    <td colspan="4"><x-empty-state title="Всего достаточно" icon="bx-check-circle" /></td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="receipt-form" action="{{ route('main.warehouse.receipts.goods.store') }}" method="POST">
        @csrf

        <div class="alert alert-info" role="note">
            <i class="bx bx-info-circle alert-icon" aria-hidden="true"></i>
            <div>Выберите тип товаров для добавления на склад и заполните детали прихода.</div>
        </div>

        @foreach ($sections as $key => $section)
            <div class="card mb-3" data-receipt-section="{{ $key }}">
                <div class="card-header">
                    <label class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" id="{{ $key }}-toggle" checked
                               data-section-toggle aria-controls="{{ $key }}-body">
                        <span class="form-check-label card-title">
                            <i class="bx {{ $section['icon'] }} text-{{ $section['colour'] }} me-1" aria-hidden="true"></i>{{ $section['title'] }}
                        </span>
                    </label>
                    <div class="card-actions">
                        <span class="badge bg-{{ $section['colour'] }}-lt" title="Строк в приходе" data-section-count>0</span>
                    </div>
                </div>
                <div class="card-body" id="{{ $key }}-body" data-section-body>
                    <label class="form-label" for="{{ $section['select'] }}">{{ $section['label'] }}</label>
                    <select id="{{ $section['select'] }}" class="form-select select2" multiple
                            data-placeholder="Выберите товары" data-allow-clear="true" data-section-select>
                        @foreach ($section['items'] as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>

                    <div class="vstack gap-3 mt-3" data-section-lines></div>

                    <div data-section-empty>
                        <x-empty-state :title="'Добавьте '.mb_strtolower($section['title'])" :text="$section['empty']" :icon="$section['icon']" />
                    </div>
                </div>
            </div>
        @endforeach

        <div class="text-end">
            <button type="submit" class="btn btn-primary"><i class="bx bx-save" aria-hidden="true"></i>Сохранить приход</button>
        </div>
    </form>

    {{-- A line of the receipt. __LINE__ becomes the line's key: a running number for medications (a drug may come in
         several batches, "+ партия"), the item's id for supplies and household materials. --}}
    <template id="receipt-medications-line">
        <div class="card card-sm" data-line>
            <div class="card-status-start bg-primary"></div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <h3 class="card-title mb-0" data-line-title></h3>
                    <div class="ms-auto btn-list flex-nowrap">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-add-batch title="Ещё одна партия этого препарата">+ партия</button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-icon" data-remove-line aria-label="Убрать строку">
                            <i class="bx bx-x" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="medications[__LINE__][id]" data-line-id>

                @foreach ([['', $batchFields, 'col-md-6'], ['Упаковка', $packageFields, 'col-md-6 col-xl-4'], ['Стоимость и поставки', $priceFields, 'col-md-6 col-xl-4'], ['Дополнительные поля', $extraFields, 'col-md-6 col-xl-4']] as [$heading, $fields, $column])
                    @if ($heading !== '')
                        <h4 class="subheader mt-4 mb-2">{{ $heading }}</h4>
                    @endif
                    <div class="row g-3">
                        @if ($heading === 'Упаковка')
                            <div class="{{ $column }}">
                                <label class="form-label" for="medication-__LINE__-packaging_type">Тип упаковки</label>
                                <select id="medication-__LINE__-packaging_type" name="medications[__LINE__][packaging_type]" class="form-select">
                                    @foreach (\App\Enums\PackagingType::options() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @foreach ($fields as $name => [$label, $required, $icon, $input])
                            @php
                                $field = new \Illuminate\View\ComponentAttributeBag([
                                    'id' => "medication-__LINE__-{$name}",
                                    'name' => "medications[__LINE__][{$name}]",
                                    ...$input,
                                ]);
                            @endphp
                            <div class="{{ $column }}">
                                <label @class(['form-label', 'required' => $required]) for="medication-__LINE__-{{ $name }}">{{ $label }}</label>
                                @if ($icon)
                                    <div class="input-group input-group-flat">
                                        <span class="input-group-text"><i class="bx {{ $icon }}" aria-hidden="true"></i></span>
                                        <input {{ $field->class('form-control') }}>
                                    </div>
                                @else
                                    <input {{ $field->class('form-control') }}>
                                @endif
                            </div>
                        @endforeach

                        @if ($heading === 'Стоимость и поставки')
                            <div class="{{ $column }}">
                                <label class="form-label" for="medication-__LINE__-supplier_id">Поставщик</label>
                                <select id="medication-__LINE__-supplier_id" name="medications[__LINE__][supplier_id]" class="form-select">
                                    <option value="">Не указан</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </template>

    @foreach (['supplies' => 'success', 'household' => 'warning'] as $key => $colour)
        <template id="receipt-{{ $key }}-line">
            <div class="card card-sm" data-line>
                <div class="card-status-start bg-{{ $colour }}"></div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <h3 class="card-title mb-0" data-line-title></h3>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-icon ms-auto" data-remove-line aria-label="Убрать строку">
                            <i class="bx bx-x" aria-hidden="true"></i>
                        </button>
                    </div>
                    <input type="hidden" name="{{ $key }}[__LINE__][id]" data-line-id>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required" for="{{ $key }}-__LINE__-quantity">Количество</label>
                            <div class="input-group input-group-flat">
                                <span class="input-group-text"><i class="bx bx-calculator" aria-hidden="true"></i></span>
                                <input type="number" id="{{ $key }}-__LINE__-quantity" name="{{ $key }}[__LINE__][quantity]" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    @endforeach
@endsection

@section('scripts')
    @vite('resources/js/pages/main-warehouse-receipts.js')
@endsection
