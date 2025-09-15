<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\WarehouseRequest;
use App\Models\HouseholdMaterial;
use App\Models\MainWarehouse;
use App\Models\MedicalSupply;
use App\Models\Medication;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseAccounting;
use App\Models\WarehouseType;
use App\Services\WarehouseService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    protected WarehouseService $service;

    public function __construct(WarehouseService $service)
    {
        $this->middleware(['permission:list_warehouses'])->only('index');
        $this->middleware(['permission:create_warehouses'])->only(['create', 'store']);
        $this->middleware(['permission:edit_warehouses'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_warehouses'])->only('destroy');

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
        $supplies = MedicalSupply::query()->get();

        return view('admin.warehouses.create', compact('types', 'medications', 'suppliers', 'supplies'));
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
        $supplies = MedicalSupply::query()->get();

        return view('admin.warehouses.edit', compact('warehouse', 'types', 'medications', 'suppliers', 'supplies'));
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


    public function remains(Request $request): View
    {
        $warehouse = MainWarehouse::query()->first();
        $type = request('type');
        $medications = collect();
        $supplies = collect();
        $household = collect();

        if (!$type || $type === 'medications') {
            $medications = WarehouseAccounting::with('medication')
                ->when($request->filled('warehouse_id'), function ($q) use ($request) {
                    $q->where('warehouse_id', $request->input('warehouse_id'));
                })
                ->with('warehouse')
                ->get();
        }

        if (!$type || $type === 'supplies') {
            $supplies = MedicalSupply::with([
                'warehouse' => function ($q) {
                    $q->withPivot('quantity');
                }
            ])
                ->when($request->filled('warehouse_id'), function ($q) use ($request) {
                    $q->whereHas('warehouse', function ($query) use ($request) {
                        $query->where('warehouses.id', $request->input('warehouse_id'));
                    });
                })
                ->get();
        }

        if (!$type || $type === 'household') {
            $household = HouseholdMaterial::with([
                'warehouse' => function ($q) {
                    $q->withPivot('quantity');
                }
            ])
                ->when($request->filled('warehouse_id'), function ($q) use ($request) {
                    $q->whereHas('warehouse', function ($query) use ($request) {
                        $query->where('warehouses.id', $request->input('warehouse_id'));
                    });
                })
                ->get();
        }

        return view('admin.warehouses.remains', compact(
            'warehouse', 'medications', 'supplies', 'household', 'type'
        ));
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

    public function supplies(Request $request): View
    {
        $supplies = MedicalSupply::with([
            'warehouse' => function ($q) {
                $q->withPivot('quantity');
            }
        ])
            ->when($request->filled('warehouse_id'), function ($q) use ($request) {
                $q->whereHas('warehouse', function ($query) use ($request) {
                    $query->where('warehouses.id', $request->input('warehouse_id'));
                });
            })
            ->paginate(30);

        return view('admin.warehouses.supplies', compact('supplies'));
    }

    public function orderShow(): View
    {
        $medications = Medication::query()->whereHas('mainWarehouses')->get();
        $supplies = MedicalSupply::query()->whereHas('mainWarehouses')->get();
        $householdMaterial = HouseholdMaterial::query()->whereHas('mainWarehouses')->get();

        $warehouses = Warehouse::query()->get();

        return view(
            'admin.warehouses.order',
            compact('medications',  'supplies', 'householdMaterial', 'warehouses'));
    }
}
