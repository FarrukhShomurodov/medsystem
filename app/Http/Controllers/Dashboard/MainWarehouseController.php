<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\MainWarehouseReceiptsGoodsRequest;
use App\Http\Requests\Stock\StockRemainsFilterRequest;
use App\Models\HouseholdMaterial;
use App\Models\MainWarehouse;
use App\Models\MainWarehouseMedication;
use App\Models\MedicalSupply;
use App\Models\Medication;
use App\Models\Supplier;
use App\Queries\Stock\RemainsQuery;
use App\Services\Stock\MainWarehouseService;
use App\Services\Stock\StockSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MainWarehouseController extends Controller
{
    public function __construct(
        private readonly MainWarehouseService $service,
        private readonly RemainsQuery $remains,
    ) {}

    /**
     * What is left in the main warehouse: medication batches, supplies and household materials.
     * A batch is marked "мало" by the units its drug has in date over all its batches (onHand).
     */
    public function remains(StockRemainsFilterRequest $request): View
    {
        $section = $request->section();
        $today = today();

        return view('admin.main_warehouses.remains', [
            'type' => $section,
            'today' => $today,
            ...$this->remains->mainWarehouse($section, $today),
        ]);
    }

    public function showMedicationRemain(MainWarehouseMedication $batch): View
    {
        abort_unless($batch->main_warehouse_id === MainWarehouse::MAIN, 404);

        $batch->load(['medication.manufacturer', 'supplier']);

        return view('admin.main_warehouses.medication_show', compact('batch'));
    }

    /** The receipt form, under the items the main warehouse runs low on. */
    public function receiptsGoods(StockRemainsFilterRequest $request): View
    {
        $runningLow = $this->remains->runningLowInMainWarehouse($request->section(), today());

        return view('admin.main_warehouses.receipts_goods', [
            'remainMedications' => $runningLow['medications'],
            'remainSupplies' => $runningLow['supplies'],
            'remainHousehold' => $runningLow['household'],
            'medications' => Medication::query()->orderBy('name')->get(['id', 'name']),
            'suppliers' => Supplier::query()->orderBy('name')->get(['id', 'name']),
            'supplies' => MedicalSupply::query()->orderBy('name')->get(['id', 'name']),
            'householdMaterial' => HouseholdMaterial::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function receiptsGoodsStore(MainWarehouseReceiptsGoodsRequest $request): RedirectResponse
    {
        $this->service->storeGoods($request->validated());

        return to_route('main.warehouse.remains')->with('success', 'Приход товаров принят на склад.');
    }

    /** An unknown section in the URL is a 404 (implicit enum binding). */
    public function discardGood(StockSection $type, int $id): RedirectResponse
    {
        $row = $this->service->writeOff($type, $id);
        $name = $row->getRelation('stockItem')?->getAttribute('name') ?? '';

        return to_route('main.warehouse.remains')
            ->with('success', "Списано со склада: «{$name}», {$row->getAttribute('quantity')} шт.");
    }
}
