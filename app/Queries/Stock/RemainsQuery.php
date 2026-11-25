<?php

namespace App\Queries\Stock;

use App\Models\MainWarehouse;
use App\Models\MainWarehouseHouseholdMaterial;
use App\Models\MainWarehouseMedicalSupply;
use App\Models\MainWarehouseMedication;
use App\Models\Medication;
use App\Models\WarehouseAccounting;
use App\Models\WarehouseHouseholdMaterial;
use App\Models\WarehouseMedicalSupply;
use App\Services\Stock\StockLot;
use App\Services\Stock\StockSection;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * What the remains pages and the receipts page list, section by section (StockSection; every
 * section when none is chosen). A section left out is listed empty.
 *
 * A medication is kept in batches, listed earliest expiry first with the units in date of each
 * drug (onHand): a batch is marked "мало" by them, over all the batches of its drug. Supplies and
 * household materials are kept in one row per item.
 */
final readonly class RemainsQuery
{
    public function __construct(private StockAlertsQuery $alerts) {}

    /**
     * The stock rows of the main warehouse with units left.
     *
     * @return array{
     *     medications: Collection<int, MainWarehouseMedication>,
     *     onHand: array<int, int>,
     *     supplies: Collection<int, MainWarehouseMedicalSupply>,
     *     household: Collection<int, MainWarehouseHouseholdMaterial>,
     * }
     */
    public function mainWarehouse(?StockSection $section, CarbonInterface $today): array
    {
        $withMedications = StockSection::Medications->isShownFor($section);

        return [
            'medications' => $withMedications
                ? MainWarehouse::main()->medicationBatches()->with('medication.manufacturer')
                    ->where('quantity', '>', 0)->orderBy('expiration_date')->orderBy('id')->get()
                : collect(),
            'onHand' => $withMedications ? $this->alerts->medicationsOnHandInMainWarehouse($today) : [],
            ...$this->mainWarehouseItems($section, '>', 0),
        ];
    }

    /**
     * What the main warehouse runs low on, listed above the receipt form: medications at or below
     * their own threshold (StockAlertsQuery::runningLowInMainWarehouse()), supply and household
     * rows at or below StockLot::LOW_STOCK.
     *
     * @return array{
     *     medications: list<array{medication: Medication, onHand: int}>,
     *     supplies: Collection<int, MainWarehouseMedicalSupply>,
     *     household: Collection<int, MainWarehouseHouseholdMaterial>,
     * }
     */
    public function runningLowInMainWarehouse(?StockSection $section, CarbonInterface $today): array
    {
        return [
            'medications' => StockSection::Medications->isShownFor($section) ? $this->alerts->runningLowInMainWarehouse($today) : [],
            ...$this->mainWarehouseItems($section, '<=', StockLot::LOW_STOCK),
        ];
    }

    /**
     * The stock rows with units left in the department warehouses, or in one of them.
     *
     * @return array{
     *     medications: Collection<int, WarehouseAccounting>,
     *     onHand: array<int, array<int, int>>,
     *     supplies: Collection<int, WarehouseMedicalSupply>,
     *     household: Collection<int, WarehouseHouseholdMaterial>,
     * }
     */
    public function departmentWarehouses(?StockSection $section, ?int $warehouseId, CarbonInterface $today): array
    {
        $withMedications = StockSection::Medications->isShownFor($section);

        return [
            'medications' => $withMedications
                ? $this->leftIn(WarehouseAccounting::query()->with(['medication', 'warehouse']), $warehouseId)
                    ->orderBy('expiration_date')->orderBy('id')->get()
                : collect(),
            'onHand' => $withMedications ? $this->alerts->medicationsOnHandInWardWarehouses($today, $warehouseId) : [],
            'supplies' => StockSection::Supplies->isShownFor($section)
                ? $this->leftIn(WarehouseMedicalSupply::query()->with(['supply', 'warehouse']), $warehouseId)->orderBy('id')->get()
                : collect(),
            'household' => StockSection::Household->isShownFor($section)
                ? $this->leftIn(WarehouseHouseholdMaterial::query()->with(['material', 'warehouse']), $warehouseId)->orderBy('id')->get()
                : collect(),
        ];
    }

    /**
     * The supply and household rows of the main warehouse whose quantity compares to the given one.
     *
     * @param  '<='|'>'  $operator
     * @return array{supplies: Collection<int, MainWarehouseMedicalSupply>, household: Collection<int, MainWarehouseHouseholdMaterial>}
     */
    private function mainWarehouseItems(?StockSection $section, string $operator, int $quantity): array
    {
        $warehouse = MainWarehouse::main();

        return [
            'supplies' => StockSection::Supplies->isShownFor($section)
                ? $warehouse->supplyStock()->with('supply')->where('quantity', $operator, $quantity)->orderBy('id')->get()
                : collect(),
            'household' => StockSection::Household->isShownFor($section)
                ? $warehouse->householdStock()->with('material')->where('quantity', $operator, $quantity)->orderBy('id')->get()
                : collect(),
        ];
    }

    /**
     * @template TRow of Model
     *
     * @param  Builder<TRow>  $rows
     * @return Builder<TRow>
     */
    private function leftIn(Builder $rows, ?int $warehouseId): Builder
    {
        return $rows
            ->when($warehouseId, fn (Builder $query, int $id) => $query->where('warehouse_id', $id))
            ->where('quantity', '>', 0);
    }
}
