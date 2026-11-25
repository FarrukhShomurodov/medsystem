<?php

namespace App\Services\Stock;

use App\Enums\StockMovementReason;
use App\Enums\SupplyRequestStatus;
use App\Exceptions\Stock\InvalidStatusTransitionException;
use App\Models\HouseholdMaterial;
use App\Models\MainWarehouse;
use App\Models\MainWarehouseHouseholdMaterial;
use App\Models\MainWarehouseMedicalSupply;
use App\Models\MainWarehouseMedication;
use App\Models\MedicalSupply;
use App\Models\Medication;
use App\Models\SupplyRequest;
use App\Models\SupplyRequestItem;
use App\Models\Warehouse;
use App\Models\WarehouseAccounting;
use App\Models\WarehouseHouseholdMaterial;
use App\Models\WarehouseMedicalSupply;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Supply requests of the department warehouses to the main warehouse (stock rules: see StockLedger).
 */
class SupplyRequestService
{
    private const string MAIN_WAREHOUSE = 'главный склад';

    public function __construct(private readonly StockLedger $ledger) {}

    /**
     * @param  array{warehouse_id: int, items: list<array{id: int, quantity: int}>}  $validated
     */
    public function store(array $validated): SupplyRequest
    {
        $warehouse = Warehouse::query()->findOrFail($validated['warehouse_id']);
        $itemClass = $warehouse->stockItemClass()
            ?? throw new LogicException("Warehouse type {$warehouse->warehouse_type_id} stores no stock items.");

        return DB::transaction(function () use ($validated, $warehouse, $itemClass): SupplyRequest {
            $request = SupplyRequest::query()->create([
                'warehouse_id' => $warehouse->id,
                'main_warehouse_id' => MainWarehouse::MAIN,
                'status' => SupplyRequestStatus::Pending,
            ]);

            foreach ($validated['items'] as $item) {
                $request->items()->create([
                    'supplyable_type' => (new $itemClass)->getMorphClass(),
                    'supplyable_id' => $item['id'],
                    'requested_quantity' => $item['quantity'],
                ]);
            }

            return $request;
        });
    }

    /**
     * Moves exactly the requested quantities from the main warehouse to the department warehouse,
     * medication batches earliest expiry first. All items move or none (a shortage throws and rolls
     * back), and a request is decided once: approving it again, or after a rejection, is refused.
     *
     * @throws InvalidStatusTransitionException when the request was decided already
     */
    public function approve(SupplyRequest $request): void
    {
        DB::transaction(function () use ($request): void {
            $request = $this->lockUndecided($request, SupplyRequestStatus::Approved);
            $request->load('items.supplyable');

            // A fixed order of items keeps the row locks of two concurrent approvals in the same order.
            foreach ($request->items->sortBy(['supplyable_type', 'supplyable_id']) as $item) {
                $this->moveItem($request, $item);
                $item->update(['approved_quantity' => $item->requested_quantity]);
            }

            $request->update(['status' => SupplyRequestStatus::Approved]);
        });
    }

    /**
     * Refuses the request: no stock moves, and the department sees the reason, if one is given.
     *
     * @throws InvalidStatusTransitionException when the request was decided already
     */
    public function reject(SupplyRequest $request, ?string $reason): void
    {
        DB::transaction(function () use ($request, $reason): void {
            $this->lockUndecided($request, SupplyRequestStatus::Rejected)->update([
                'status' => SupplyRequestStatus::Rejected,
                'rejection_reason' => $reason,
            ]);
        });
    }

    /**
     * The request, locked until the transaction ends, if it may still move to the decision. A
     * concurrent approval and rejection of one request wait for each other; the later one is refused.
     *
     * @throws InvalidStatusTransitionException
     */
    private function lockUndecided(SupplyRequest $request, SupplyRequestStatus $decision): SupplyRequest
    {
        $locked = SupplyRequest::query()->whereKey($request->getKey())->lockForUpdate()->firstOrFail();

        if (! $locked->status->canTransitionTo($decision)) {
            throw new InvalidStatusTransitionException('Заказ', $locked->status->label(), $decision->label());
        }

        return $locked;
    }

    private function moveItem(SupplyRequest $request, SupplyRequestItem $item): void
    {
        $name = $item->supplyable?->getAttribute('name') ?? "#{$item->supplyable_id}";
        $warehouseId = $request->warehouse_id;

        match ($item->supplyable_type) {
            (new Medication)->getMorphClass() => $this->moveMedication($request, $item, $name),
            (new MedicalSupply)->getMorphClass() => $this->moveRow(
                $request,
                $item,
                $name,
                MainWarehouseMedicalSupply::query()->where('supply_id', $item->supplyable_id),
                WarehouseMedicalSupply::query(),
                ['warehouse_id' => $warehouseId, 'supply_id' => $item->supplyable_id],
            ),
            (new HouseholdMaterial)->getMorphClass() => $this->moveRow(
                $request,
                $item,
                $name,
                MainWarehouseHouseholdMaterial::query()->where('household_material_id', $item->supplyable_id),
                WarehouseHouseholdMaterial::query(),
                ['warehouse_id' => $warehouseId, 'household_material_id' => $item->supplyable_id],
            ),
            default => throw new LogicException("Unknown supply request item type {$item->supplyable_type}."),
        };
    }

    /** Each main warehouse batch taken lands in the department as the same batch. */
    private function moveMedication(SupplyRequest $request, SupplyRequestItem $item, string $name): void
    {
        $batches = MainWarehouseMedication::query()
            ->where('main_warehouse_id', $request->main_warehouse_id)
            ->where('medication_id', $item->supplyable_id)
            ->unexpired()
            ->orderBy('expiration_date')
            ->orderBy('id');

        $taken = $this->ledger->take($batches, $item->requested_quantity, StockMovementReason::SupplyRequest, $request, $name, self::MAIN_WAREHOUSE);

        foreach ($taken as ['lot' => $batch, 'quantity' => $quantity]) {
            $lot = $this->ledger->lot(WarehouseAccounting::query(), [
                'warehouse_id' => $request->warehouse_id,
                'medication_id' => $item->supplyable_id,
                'batch_number' => $batch->batch_number,
                'expiration_date' => $batch->expiration_date->toDateString(),
            ]);
            $lot->date_of_receipt ??= today();

            $this->ledger->put($lot, $quantity, StockMovementReason::SupplyRequest, $request);
        }
    }

    /**
     * Supplies and household materials have one row per item on each side.
     *
     * @template TMain of MainWarehouseMedicalSupply|MainWarehouseHouseholdMaterial
     * @template TDepartment of WarehouseMedicalSupply|WarehouseHouseholdMaterial
     *
     * @param  Builder<TMain>  $mainRows
     * @param  Builder<TDepartment>  $departmentRows
     * @param  array<string, int>  $departmentKey
     */
    private function moveRow(
        SupplyRequest $request,
        SupplyRequestItem $item,
        string $name,
        Builder $mainRows,
        Builder $departmentRows,
        array $departmentKey,
    ): void {
        $mainRows->where('main_warehouse_id', $request->main_warehouse_id)->orderBy('id');

        $this->ledger->take($mainRows, $item->requested_quantity, StockMovementReason::SupplyRequest, $request, $name, self::MAIN_WAREHOUSE);
        $this->ledger->put($this->ledger->lot($departmentRows, $departmentKey), $item->requested_quantity, StockMovementReason::SupplyRequest, $request);
    }
}
