<?php

namespace App\Services;

use App\Enums\SupplyRequestStatus;
use App\Enums\TransferStatus;
use App\Exceptions\Stock\StockException;
use App\Models\SupplyRequest;
use App\Models\Transfer;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class WarehouseService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(array $validated): Warehouse
    {
        return DB::transaction(function () use ($validated): Warehouse {
            $warehouse = Warehouse::query()->create($validated);
            $validated['warehouse_id'] = $warehouse->id;

            $warehouse->contact()->create($validated);
            $warehouse->capacity()->create($validated);
            $warehouse->storageHandling()->create($validated);

            return $warehouse;
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     *
     * @throws StockException when it would change the type of a warehouse in use (ensureTypeChangeable())
     */
    public function update(Warehouse $warehouse, array $validated): Warehouse
    {
        return DB::transaction(function () use ($validated, $warehouse): Warehouse {
            if (isset($validated['warehouse_type_id'])) {
                $this->ensureTypeChangeable($warehouse, (int) $validated['warehouse_type_id']);
            }

            $warehouse->update(array_intersect_key($validated, array_flip($warehouse->getFillable())));
            $validated['warehouse_id'] = $warehouse->id;

            $warehouse->contact()->updateOrCreate(['warehouse_id' => $warehouse->id], array_intersect_key($validated, array_flip($warehouse->contact()->getRelated()->getFillable())));
            $warehouse->capacity()->updateOrCreate(['warehouse_id' => $warehouse->id], array_intersect_key($validated, array_flip($warehouse->capacity()->getRelated()->getFillable())));
            $warehouse->storageHandling()->updateOrCreate(['warehouse_id' => $warehouse->id], array_intersect_key($validated, array_flip($warehouse->storageHandling()->getRelated()->getFillable())));

            return $warehouse;
        });
    }

    public function destroy(Warehouse $warehouse): void
    {
        $warehouse->delete();
    }

    /**
     * The type decides what a warehouse orders, stores, transfers and dispenses
     * (WarehouseType::STOCK_ITEMS). Under another type its batches could no longer be transferred
     * or ordered against, and inpatient prescriptions would silently be taken from the main
     * warehouse. So the type stays once the warehouse holds stock or held it (a batch at zero is
     * refilled when a dispensed prescription is returned), or has an undecided order or transfer.
     *
     * @throws StockException
     */
    private function ensureTypeChangeable(Warehouse $warehouse, int $typeId): void
    {
        // Locked, so two saves of the form check and change the type one after the other.
        $currentTypeId = Warehouse::query()->whereKey($warehouse->getKey())->lockForUpdate()->value('warehouse_type_id');

        if ($currentTypeId === $typeId) {
            return;
        }

        $inUse = $warehouse->accounting()->exists()
            || $warehouse->supplyStock()->exists()
            || $warehouse->householdStock()->exists()
            || SupplyRequest::query()->where('warehouse_id', $warehouse->getKey())->where('status', SupplyRequestStatus::Pending)->exists()
            || Transfer::query()->where('from_warehouse_id', $warehouse->getKey())->where('status', TransferStatus::Pending)->exists();

        if ($inUse) {
            throw new StockException('Тип склада нельзя изменить: на складе есть или были остатки, либо есть незавершённые заказы и переводы.');
        }
    }
}
