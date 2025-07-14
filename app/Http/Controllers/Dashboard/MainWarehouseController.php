<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\MainWarehouseReceiptsGoodsRequest;
use App\Models\HouseholdMaterial;
use App\Models\MainWarehouse;
use App\Models\MedicalSupply;
use App\Models\Medication;
use App\Models\Supplier;
use App\Services\MainWarehouseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MainWarehouseController extends Controller
{
    protected MainWarehouseService $service;

    public function __construct(MainWarehouseService $service)
    {
        $this->service = $service;
    }

    public function remains(): View
    {
        $warehouse = MainWarehouse::query()->first();
        $type = request('type');

        $medications = collect();
        $supplies = collect();
        $household = collect();

        if (!$type || $type === 'medications') {
            $medications = $warehouse->medication()->withPivot([
                'quantity', 'expiration_date', 'date_of_receipt', 'batch_number',
                'retail_price', 'wholesale_price', 'supplier_id',
                'packaging_type', 'quantity_per_package', 'package_size', 'packaging_date'
            ])->get();
        }

        if (!$type || $type === 'supplies') {
            $supplies = $warehouse->medicalSupplies()->get();
        }

        if (!$type || $type === 'household') {
            $household = $warehouse->householdMaterial()->get();
        }

        return view('admin.main_warehouses.remains', compact(
            'warehouse', 'medications', 'supplies', 'household', 'type'
        ));
    }


    public function receiptsGoods(): View
    {
        $warehouse = MainWarehouse::query()->first();
        $type = request('type');

        $remainMedications = collect();
        $remainSupplies = collect();
        $remainHousehold = collect();

        if (!$type || $type === 'medications') {
            $remainMedications = $warehouse->medication()->withPivot([
                'quantity', 'expiration_date', 'date_of_receipt', 'batch_number',
                'retail_price', 'wholesale_price', 'supplier_id',
                'packaging_type', 'quantity_per_package', 'package_size', 'packaging_date'
            ])->wherePivot('quantity', '<', 20)->get();
        }

        if (!$type || $type === 'supplies') {
            $remainSupplies = $warehouse->medicalSupplies()->wherePivot('quantity', '<', 20)->get();
        }

        if (!$type || $type === 'household') {
            $remainHousehold = $warehouse->householdMaterial()->wherePivot('quantity', '<', 20)->get();
        }

        $medications = Medication::query()->get();
        $suppliers = Supplier::query()->get();
        $supplies = MedicalSupply::query()->get();
        $householdMaterial = HouseholdMaterial::query()->get();

        return view(
            'admin.main_warehouses.receipts_goods',
            compact('medications', 'suppliers', 'supplies', 'householdMaterial', 'remainMedications', 'remainSupplies', 'remainHousehold')
        );
    }

    public function receiptsGoodsStore(MainWarehouseReceiptsGoodsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->storeGoods((array)$validated);

        return redirect()->route('main.warehouse.remains')->with(['success' => 'Товары успешно добавлены!']);
    }

    public function discardGood(string $type, $id): RedirectResponse
    {
        $this->service->discardGood($type, $id);

        return redirect()->route('main.warehouse.remains')->with(['success' => 'Товар был списан со склада!']);
    }
}
