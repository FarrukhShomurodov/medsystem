<?php

namespace App\Http\Controllers\Dashboard;

use App\Exceptions\Stock\StockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\StockRemainsFilterRequest;
use App\Http\Requests\WarehouseRequest;
use App\Models\HouseholdMaterial;
use App\Models\MainWarehouse;
use App\Models\MedicalSupply;
use App\Models\Medication;
use App\Models\Warehouse;
use App\Models\WarehouseAccounting;
use App\Models\WarehouseMedicalSupply;
use App\Models\WarehouseType;
use App\Queries\Stock\RemainsQuery;
use App\Services\WarehouseService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class WarehouseController extends Controller implements HasMiddleware
{
    public function __construct(private readonly WarehouseService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_warehouses', only: ['index']),
            new Middleware('permission:view_warehouses', only: ['show']),
            new Middleware('permission:create_warehouses', only: ['create', 'store']),
            new Middleware('permission:edit_warehouses', only: ['edit', 'update']),
            new Middleware('permission:delete_warehouses', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $warehouses = Warehouse::query()
            ->select(['id', 'name', 'warehouse_type_id'])
            ->with('type')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.warehouses.index', compact('warehouses'));
    }

    public function show(Warehouse $warehouse): View
    {
        $warehouse->load(['type', 'contact', 'capacity', 'storageHandling']);

        return view('admin.warehouses.show', compact('warehouse'));
    }

    public function create(): View
    {
        return view('admin.warehouses.create', ['types' => WarehouseType::query()->get()]);
    }

    public function store(WarehouseRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('warehouses.index')->with('success', 'Склад успешно добавлен!');
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('admin.warehouses.edit', ['warehouse' => $warehouse, 'types' => WarehouseType::query()->get()]);
    }

    public function update(WarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        try {
            $this->service->update($warehouse, $request->validated());
        } catch (StockException $e) {
            return back()->withInput()->withErrors(['warehouse_type_id' => $e->getMessage()]);
        }

        return to_route('warehouses.index')->with('success', 'Склад успешно обновлен!');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $this->service->destroy($warehouse);

        return to_route('warehouses.index')->with('success', 'Склад успешно удален!');
    }

    /**
     * What is left in the department warehouses (one of them with ?warehouse_id=). A batch is
     * marked "мало" by the units its drug has in date in that warehouse over all its batches.
     */
    public function remains(StockRemainsFilterRequest $request, RemainsQuery $remains): View
    {
        $section = $request->section();
        $today = today();

        return view('admin.warehouses.remains', [
            'type' => $section,
            'today' => $today,
            ...$remains->departmentWarehouses($section, $request->warehouseId(), $today),
        ]);
    }

    /** Medication batches of the department warehouses, earliest expiry first. */
    public function medications(Request $request): View
    {
        $accountings = $this->inWarehouse(WarehouseAccounting::query()->with(['medication', 'warehouse']), $request)
            ->orderBy('expiration_date')
            ->orderBy('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.warehouses.medications', compact('accountings'));
    }

    public function supplies(Request $request): View
    {
        $supplies = $this->inWarehouse(WarehouseMedicalSupply::query()->with(['supply', 'warehouse']), $request)
            ->orderBy('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.warehouses.supplies', compact('supplies'));
    }

    /** The supply request form: items the main warehouse has in stock, by warehouse type. */
    public function orderShow(): View
    {
        return view('admin.warehouses.order', [
            'medications' => Medication::query()->inMainWarehouseStock()->orderBy('name')->get(['id', 'name']),
            'supplies' => MedicalSupply::query()
                ->whereHas('mainWarehouses', fn (Builder $warehouses) => $warehouses
                    ->where('main_warehouses.id', MainWarehouse::MAIN)
                    ->where('main_warehouse_medical_supplies.quantity', '>', 0))
                ->orderBy('name')->get(['id', 'name']),
            'householdMaterial' => HouseholdMaterial::query()
                ->whereHas('mainWarehouses', fn (Builder $warehouses) => $warehouses
                    ->where('main_warehouses.id', MainWarehouse::MAIN)
                    ->where('main_warehouse_household_materials.quantity', '>', 0))
                ->orderBy('name')->get(['id', 'name']),
            'warehouses' => Warehouse::query()->whereIn('warehouse_type_id', array_keys(WarehouseType::STOCK_ITEMS))->orderBy('name')->get(),
        ]);
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $rows
     * @return Builder<TModel>
     */
    private function inWarehouse(Builder $rows, Request $request): Builder
    {
        return $rows->when($request->integer('warehouse_id'), fn (Builder $query, int $warehouseId) => $query->where('warehouse_id', $warehouseId));
    }
}
