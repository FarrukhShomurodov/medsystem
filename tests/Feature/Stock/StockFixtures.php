<?php

namespace Tests\Feature\Stock;

use App\Models\MainWarehouse;
use App\Models\MainWarehouseMedication;
use App\Models\Medication;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseAccounting;
use App\Models\WarehouseType;
use Illuminate\Database\Eloquent\Model;

/**
 * Stock records for the stock tests. They need the seeded warehouse types and main warehouse
 * (WarehouseSeeder), whose ids the code relies on.
 */
final class StockFixtures
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function mainBatch(Medication $medication, int $quantity, string $expires, array $attributes = []): MainWarehouseMedication
    {
        return MainWarehouseMedication::factory()->create([
            'main_warehouse_id' => MainWarehouse::MAIN,
            'medication_id' => $medication->id,
            'quantity' => $quantity,
            'expiration_date' => $expires,
            ...$attributes,
        ]);
    }

    public static function departmentWarehouse(int $type = WarehouseType::MEDICATIONS): Warehouse
    {
        return Warehouse::factory()->create(['warehouse_type_id' => $type]);
    }

    public static function departmentBatch(Warehouse $warehouse, Medication $medication, int $quantity, string $expires): WarehouseAccounting
    {
        return WarehouseAccounting::factory()->create([
            'warehouse_id' => $warehouse->id,
            'medication_id' => $medication->id,
            'quantity' => $quantity,
            'expiration_date' => $expires,
        ]);
    }

    /**
     * An expiry date relative to today: fixed dates would expire under the tests, and the stock
     * code refuses expired batches.
     */
    public static function monthsAhead(int $months): string
    {
        return today()->addMonths($months)->toDateString();
    }

    /**
     * The accounts the ledger recorded as the authors of the stock movements, each once.
     *
     * @return list<int|null>
     */
    public static function movementAuthors(): array
    {
        return StockMovement::query()->distinct()->orderBy('admin_id')->pluck('admin_id')->all();
    }

    /** The signed quantities the ledger recorded for a stock row, oldest first. */
    public static function movementsOf(Model $row): array
    {
        return StockMovement::query()
            ->whereMorphedTo('stock', $row)
            ->orderBy('id')
            ->get()
            ->map(fn (StockMovement $movement): array => [$movement->reason->value, $movement->quantity])
            ->all();
    }
}
