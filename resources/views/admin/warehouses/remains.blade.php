@extends('admin.layouts.app')
@use('App\Services\Stock\StockSection')

@section('title', 'Остатки складов')

@section('header')
    <x-page-header title="Остатки складов" pretitle="Склад и аптека">
        <x-slot:actions>
            @can('create_supply_requests')
                <a href="{{ route('warehouse.order') }}" class="btn btn-primary"><i class="bx bx-cart-add" aria-hidden="true"></i>Заказать</a>
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
                @if (request()->filled('warehouse_id'))
                    <input type="hidden" name="warehouse_id" value="{{ request()->integer('warehouse_id') }}">
                @endif
                <noscript><button type="submit" class="btn">Показать</button></noscript>
            </form>

            @include('admin.main_warehouses._legend')
        </div>

        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                <tr>
                    <th class="w-1">#</th>
                    <th>Склад</th>
                    <th>Тип</th>
                    <th>Название</th>
                    <th>Партия</th>
                    <th class="text-end">Количество</th>
                    <th>Срок годности</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @php $index = 1; @endphp

                {{-- "мало" counts the drug's units in date in that warehouse over all its batches --}}
                @foreach ($medications as $batch)
                    @php
                        $isExpired = $batch->isExpired($today);
                        $isExpiringSoon = ! $isExpired && $batch->medication->expiryWarningDue($batch->expiration_date, $today);
                        $isLowStock = $batch->medication->isLowStock($onHand[$batch->warehouse_id][$batch->medication_id] ?? 0);
                    @endphp
                    <tr @class(['table-danger' => $isExpired || $isExpiringSoon, 'fw-semibold' => $isExpired, 'table-warning' => ! $isExpired && ! $isExpiringSoon && $isLowStock])>
                        <td class="text-secondary">{{ $index++ }}</td>
                        <td>{{ $batch->warehouse->name }}</td>
                        <td><span class="badge bg-info-lt">Медикамент</span></td>
                        <td>{{ $batch->medication->name }}</td>
                        <td class="text-nowrap">{{ $batch->batch_number ?? '—' }}</td>
                        <td class="text-end text-nowrap">
                            @if ($isLowStock)
                                <span class="badge bg-warning-lt me-1">мало</span>
                            @endif
                            {{ $batch->quantity }}
                        </td>
                        <td class="text-nowrap">
                            {{ $batch->expiration_date->format('d.m.Y') }}
                            @if ($isExpired)
                                <span class="badge bg-danger text-white ms-1">просрочен</span>
                            @elseif ($isExpiringSoon)
                                <span class="badge bg-danger-lt ms-1">истекает</span>
                            @endif
                        </td>
                        <td>
                            @can('create_supply_requests')
                                @if ($isLowStock && ! $isExpired && ! $isExpiringSoon)
                                    <a class="btn btn-sm btn-primary" href="{{ route('warehouse.order') }}">Заказать</a>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @endforeach

                @foreach ($supplies as $row)
                    <tr @class(['table-warning' => $row->isLowStock()])>
                        <td class="text-secondary">{{ $index++ }}</td>
                        <td>{{ $row->warehouse->name }}</td>
                        <td><span class="badge bg-success-lt">Расходник</span></td>
                        <td>{{ $row->supply->name }}</td>
                        <td>—</td>
                        <td class="text-end text-nowrap">
                            @if ($row->isLowStock())
                                <span class="badge bg-warning-lt me-1">мало</span>
                            @endif
                            {{ $row->quantity }}
                        </td>
                        <td>—</td>
                        <td>
                            @can('create_supply_requests')
                                @if ($row->isLowStock())
                                    <a class="btn btn-sm btn-primary" href="{{ route('warehouse.order') }}">Заказать</a>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @endforeach

                @foreach ($household as $row)
                    <tr @class(['table-warning' => $row->isLowStock()])>
                        <td class="text-secondary">{{ $index++ }}</td>
                        <td>{{ $row->warehouse->name }}</td>
                        <td><span class="badge bg-warning-lt">Хоз. материал</span></td>
                        <td>{{ $row->material->name }}</td>
                        <td>{{ $row->material->batch_number }}</td>
                        <td class="text-end text-nowrap">
                            @if ($row->isLowStock())
                                <span class="badge bg-warning-lt me-1">мало</span>
                            @endif
                            {{ $row->quantity }}
                        </td>
                        <td>—</td>
                        <td>
                            @can('create_supply_requests')
                                @if ($row->isLowStock())
                                    <a class="btn btn-sm btn-primary" href="{{ route('warehouse.order') }}">Заказать</a>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @endforeach

                @if ($index === 1)
                    <tr>
                        <td colspan="8"><x-empty-state title="Остатков нет" icon="bx-archive" /></td>
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
