<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\WarehouseRequest;
use App\Models\Medication;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseAccounting;
use App\Models\WarehouseType;
use App\Services\WarehouseService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WarehouseController
{
    protected WarehouseService $service;

    public function __construct(WarehouseService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $warehouses = Warehouse::query()
            ->select(['id', 'name', 'warehouse_type_id'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.warehouses.index', compact('warehouses'));
    }

    public function show(Warehouse $warehouse): View
    {
        return view('admin.warehouses.show', compact('warehouse'));
    }

    public function create(): View
    {
        $types = WarehouseType::query()->get();
        $medications = Medication::query()->get();
        $suppliers = Supplier::query()->get();

        return view('admin.warehouses.create', compact('types', 'medications', 'suppliers'));
    }

    public function store(WarehouseRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('warehouses.index')->with('success', 'Склад успешно добавлен!');
    }

    public function edit(Warehouse $warehouse): View
    {
        $types = WarehouseType::query()->get();
        $medications = Medication::query()->get();
        $suppliers = Supplier::query()->get();

        return view('admin.warehouses.edit', compact('warehouse', 'types', 'medications', 'suppliers'));
    }

    public function update(WarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($warehouse, (array)$validated);

        return redirect()->route('warehouses.index')->with('success', 'Склад успешно обновлен!');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $this->service->destroy($warehouse);

        return redirect()->route('warehouses.index')->with('success', 'Склад успешно удален!');
    }

    public function medications(Request $request): View
    {
        $accountings = WarehouseAccounting::with('medication')
            ->when($request->filled('warehouse_id'), function ($q) use ($request) {
                $q->where('warehouse_id', $request->input('warehouse_id'));
            })
            ->with('packaging')
            ->with('warehouse')
            ->with('medicationCost')
            ->orderBy('expiration_date', 'ASC')
            ->paginate(30);

        return view('admin.warehouses.medications', compact('accountings'));
    }
}
