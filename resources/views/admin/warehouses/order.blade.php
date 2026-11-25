@extends('admin.layouts.app')

@php
    // A warehouse orders only the items of its type (WarehouseType::STOCK_ITEMS): one item template per type.
    $productsByType = [
        \App\Models\WarehouseType::MEDICATIONS => $medications,
        \App\Models\WarehouseType::HOUSEHOLD_MATERIALS => $householdMaterial,
        \App\Models\WarehouseType::MEDICAL_SUPPLIES => $supplies,
    ];
@endphp

@section('title', 'Заказ на общий склад')

@section('header')
    <x-page-header title="Заказ на общий склад" :breadcrumbs="['Остатки складов' => auth()->user()?->can('view_warehouse_remains') ? route('warehouse.remains') : null]" />
@endsection

@section('content')
    <div class="card">
        <form action="{{ route('supply-requests.store') }}" method="POST" data-order-form>
            @csrf
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required" for="order-warehouse">Склад</label>
                        <select id="order-warehouse" class="form-select" required aria-describedby="order-warehouse-hint" data-order-warehouse>
                            <option value="">Выберите склад</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" data-type="{{ $warehouse->warehouse_type_id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-hint" id="order-warehouse-hint">Заказ собирается из товаров этого склада, поэтому после выбора склад не меняется.</div>
                        <input type="hidden" name="warehouse_id" value="" data-order-warehouse-id>
                    </div>
                </div>

                <div class="vstack gap-3 mt-3" data-order-items></div>

                <button type="button" class="btn btn-outline-primary btn-sm mt-3" data-order-add>
                    <i class="bx bx-plus" aria-hidden="true"></i>Добавить товар
                </button>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-success"><i class="bx bx-package" aria-hidden="true"></i>Заказать</button>
            </div>
        </form>
    </div>

    {{-- An item of the order; __INDEX__ becomes the item's number (resources/js/pages/warehouse-order.js). --}}
    @foreach ($productsByType as $typeId => $products)
        <template id="order-item-{{ $typeId }}">
            <div class="row g-2 align-items-end" data-order-item>
                <div class="col-md-6">
                    <label class="form-label required" for="order-item-__INDEX__-id">Товар</label>
                    <select id="order-item-__INDEX__-id" name="items[__INDEX__][id]" class="form-select" required>
                        <option value="">Выберите товар</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label required" for="order-item-__INDEX__-quantity">Кол-во</label>
                    <input type="number" id="order-item-__INDEX__-quantity" name="items[__INDEX__][quantity]" class="form-control" min="1" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger w-100" data-order-remove>
                        <i class="bx bx-x" aria-hidden="true"></i>Убрать
                    </button>
                </div>
            </div>
        </template>
    @endforeach
@endsection

@section('scripts')
    @vite('resources/js/pages/warehouse-order.js')
@endsection
