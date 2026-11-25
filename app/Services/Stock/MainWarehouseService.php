<?php

namespace App\Services\Stock;

use App\Enums\StockMovementReason;
use App\Models\MainWarehouse;
use App\Models\MainWarehouseHouseholdMaterial;
use App\Models\MainWarehouseMedicalSupply;
use App\Models\MainWarehouseMedication;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Receipts into and write-offs from the main warehouse (stock rules: see StockLedger).
 */
class MainWarehouseService
{
    public function __construct(private readonly StockLedger $ledger) {}

    /**
     * Every medication line becomes a batch of its own, even when one receipt brings two batches
     * of a drug. Supplies and household materials are added to their item's row, taken in item
     * order (ADR 4): two receipts listing the same items in another order cannot deadlock.
     *
     * @param  array<string, mixed>  $validated
     */
    public function storeGoods(array $validated): void
    {
        $warehouse = MainWarehouse::main();

        DB::transaction(function () use ($validated, $warehouse): void {
            foreach ($validated['medications'] ?? [] as $line) {
                $batch = new MainWarehouseMedication([
                    'main_warehouse_id' => $warehouse->id,
                    'medication_id' => $line['id'],
                    'quantity' => 0,
                    'expiration_date' => $line['expiration_date'],
                    'date_of_receipt' => $line['date_of_receipt'],
                    'batch_number' => $line['batch_number'],
                    'retail_price' => $line['retail_price'],
                    'wholesale_price' => $line['wholesale_price'],
                    'supplier_id' => $line['supplier_id'] ?? null,
                    'packaging_type' => $line['packaging_type'] ?? null,
                    'quantity_per_package' => $line['quantity_per_package'] ?? null,
                    'package_size' => $line['package_size'] ?? null,
                    'markup' => $line['markup'] ?? null,
                    'total_price' => $line['total_price'] ?? null,
                    'incoming_price_total' => $line['incoming_price_total'] ?? null,
                    'incoming_sum' => $line['incoming_sum'] ?? null,
                    'incoming_price_per_unit' => $line['incoming_price_per_unit'] ?? null,
                ]);

                $this->ledger->put($batch, (int) $line['quantity'], StockMovementReason::Receipt, null);
            }

            foreach ($this->inItemOrder($validated['supplies'] ?? []) as $line) {
                $row = $this->ledger->lot(MainWarehouseMedicalSupply::query(), [
                    'main_warehouse_id' => $warehouse->id,
                    'supply_id' => $line['id'],
                ]);
                $this->ledger->put($row, $line['quantity'], StockMovementReason::Receipt, null);
            }

            foreach ($this->inItemOrder($validated['household'] ?? []) as $line) {
                $row = $this->ledger->lot(MainWarehouseHouseholdMaterial::query(), [
                    'main_warehouse_id' => $warehouse->id,
                    'household_material_id' => $line['id'],
                ]);
                $this->ledger->put($row, $line['quantity'], StockMovementReason::Receipt, null);
            }
        });
    }

    /**
     * The supply or household lines of a receipt (validated: an id and a quantity each), sorted by
     * the item, the order their rows are locked in.
     *
     * @param  iterable<array<string, mixed>>  $lines
     * @return list<array{id: int, quantity: int}>
     */
    private function inItemOrder(iterable $lines): array
    {
        $sorted = [];

        foreach ($lines as $line) {
            $sorted[] = ['id' => (int) $line['id'], 'quantity' => (int) $line['quantity']];
        }

        usort($sorted, fn (array $a, array $b): int => $a['id'] <=> $b['id']);

        return $sorted;
    }

    /**
     * Writes off exactly one stock line of the main warehouse: a medication batch, or the row of
     * a supply or household material. Returns the removed row for the confirmation message.
     */
    public function writeOff(StockSection $section, int $rowId): Model&StockLot
    {
        $class = $section->mainWarehouseRow();

        return DB::transaction(function () use ($class, $rowId): Model&StockLot {
            $row = $class::query()
                ->where('main_warehouse_id', MainWarehouse::MAIN)
                ->whereKey($rowId)
                ->lockForUpdate()
                ->firstOrFail();

            $row->loadMissing('stockItem');
            $this->ledger->writeOff($row);

            return $row;
        });
    }
}
