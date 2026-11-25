@extends('admin.layouts.app')
@use('App\Support\Display')
@use('App\Services\Stock\StockSection')

@section('title', 'Остатки общего склада')

@section('header')
    <x-page-header title="Остатки общего склада" pretitle="Склад и аптека">
        <x-slot:actions>
            @can('edit_main_warehouse')
                <a href="{{ route('main.warehouse.receipts.goods.index') }}" class="btn btn-primary">
                    <i class="bx bx-download" aria-hidden="true"></i>Приход
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    <div class="card">
        <div class="card-header flex-wrap gap-3">
            <form method="GET" class="d-flex align-items-center gap-2">
                <label for="type" class="form-label mb-0">Тип</label>
                <select name="type" id="type" class="form-select w-auto" data-autosubmit>
                    <option value="">Все</option>
                    @foreach (StockSection::cases() as $section)
                        <option value="{{ $section->value }}" @selected($type === $section)>{{ $section->label() }}</option>
                    @endforeach
                </select>
                <noscript><button type="submit" class="btn">Показать</button></noscript>
            </form>

            @include('admin.main_warehouses._legend')
        </div>

        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                <tr>
                    <th class="w-1">#</th>
                    <th>Тип</th>
                    <th>Название</th>
                    <th>Производитель</th>
                    <th>Форма выпуска</th>
                    <th>Партия</th>
                    <th>Срок годности</th>
                    <th class="text-end">Количество</th>
                    <th>Кол-во в упаковке</th>
                    <th class="text-end">Розн. цена</th>
                    <th class="text-end">Опт. цена</th>
                    <th class="text-end">Наценка</th>
                    <th class="text-end">Цена вход. итого</th>
                    <th class="text-end">Цена вход. за ед.</th>
                    <th class="text-end">Сумма входящая</th>
                    <th class="text-end">Итоговая цена</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @php $index = 1; @endphp

                {{-- Medication batches: one row per received batch; "мало" counts the drug's units in date over all its batches --}}
                @foreach ($medications as $batch)
                    @php
                        $medication = $batch->medication;
                        $isExpired = $batch->isExpired($today);
                        $isExpiringSoon = ! $isExpired && $medication->expiryWarningDue($batch->expiration_date, $today);
                        $isLowStock = $medication->isLowStock($onHand[$batch->medication_id] ?? 0);
                    @endphp
                    <tr @class(['table-danger' => $isExpired || $isExpiringSoon, 'fw-semibold' => $isExpired, 'table-warning' => ! $isExpired && ! $isExpiringSoon && $isLowStock])>
                        <td class="text-secondary">{{ $index++ }}</td>
                        <td><span class="badge bg-info-lt">Медикамент</span></td>
                        <td>
                            <a href="{{ route('main.warehouse.show.medication.remain', $batch) }}">{{ $medication->name }}</a>
                        </td>
                        <td>{{ $medication->manufacturer?->name ?? '—' }}</td>
                        <td>{{ $medication->release_form }}</td>
                        <td class="text-nowrap">{{ $batch->batch_number }}</td>
                        <td class="text-nowrap">
                            {{ $batch->expiration_date->format('d.m.Y') }}
                            @if ($isExpired)
                                <span class="badge bg-danger text-white ms-1">просрочен</span>
                            @elseif ($isExpiringSoon)
                                <span class="badge bg-danger-lt ms-1">истекает</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            @if ($isLowStock)
                                <span class="badge bg-warning-lt me-1">мало</span>
                            @endif
                            {{ $batch->quantity }}
                        </td>
                        <td>{{ $batch->quantity_per_package ?? '—' }}</td>
                        <td class="text-end text-nowrap">{{ Display::money($batch->retail_price) }}</td>
                        <td class="text-end text-nowrap">{{ Display::money($batch->wholesale_price) }}</td>
                        <td class="text-end text-nowrap">{{ Display::percent($batch->markup) }}</td>
                        <td class="text-end text-nowrap">{{ Display::money($batch->incoming_price_total) }}</td>
                        <td class="text-end text-nowrap">{{ Display::unitPrice($batch->incoming_price_per_unit) }}</td>
                        <td class="text-end text-nowrap">{{ Display::money($batch->incoming_sum) }}</td>
                        <td class="text-end text-nowrap">{{ Display::money($batch->total_price) }}</td>
                        <td>
                            @can('edit_main_warehouse')
                                @include('admin.main_warehouses._write_off', ['type' => StockSection::Medications, 'row' => $batch, 'name' => $medication->name, 'due' => $isExpired])
                            @endcan
                        </td>
                    </tr>
                @endforeach

                @foreach ($supplies as $row)
                    <tr @class(['table-warning' => $row->isLowStock()])>
                        <td class="text-secondary">{{ $index++ }}</td>
                        <td><span class="badge bg-success-lt">Расходник</span></td>
                        <td>{{ $row->supply->name }}</td>
                        <td>—</td>
                        <td>—</td>
                        <td>—</td>
                        <td>—</td>
                        <td class="text-end text-nowrap">
                            @if ($row->isLowStock())
                                <span class="badge bg-warning-lt me-1">мало</span>
                            @endif
                            {{ $row->quantity }}
                        </td>
                        <td colspan="8"></td>
                        <td>
                            @can('edit_main_warehouse')
                                @include('admin.main_warehouses._write_off', ['type' => StockSection::Supplies, 'row' => $row, 'name' => $row->supply->name])
                            @endcan
                        </td>
                    </tr>
                @endforeach

                @foreach ($household as $row)
                    <tr @class(['table-warning' => $row->isLowStock()])>
                        <td class="text-secondary">{{ $index++ }}</td>
                        <td><span class="badge bg-warning-lt">Хоз. материал</span></td>
                        <td>{{ $row->material->name }}</td>
                        <td>—</td>
                        <td>—</td>
                        <td>{{ $row->material->batch_number }}</td>
                        <td>—</td>
                        <td class="text-end text-nowrap">
                            @if ($row->isLowStock())
                                <span class="badge bg-warning-lt me-1">мало</span>
                            @endif
                            {{ $row->quantity }}
                        </td>
                        <td colspan="8"></td>
                        <td>
                            @can('edit_main_warehouse')
                                @include('admin.main_warehouses._write_off', ['type' => StockSection::Household, 'row' => $row, 'name' => $row->material->name])
                            @endcan
                        </td>
                    </tr>
                @endforeach

                @if ($index === 1)
                    <tr>
                        <td colspan="17"><x-empty-state title="На складе ничего нет" icon="bx-store" /></td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    @include('admin.main_warehouses._autosubmit')
@endsection
