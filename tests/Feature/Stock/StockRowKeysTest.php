<?php

use App\Enums\StockMovementReason;
use App\Models\HouseholdMaterial;
use App\Models\MainWarehouse;
use App\Models\MainWarehouseMedicalSupply;
use App\Models\MedicalSupply;
use App\Models\Medication;
use App\Models\Pharmacy;
use App\Models\StockMovement;
use App\Models\WarehouseAccounting;
use App\Services\Stock\StockLedger;
use Database\Seeders\WarehouseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Pest\Expectation;
use Tests\Feature\Stock\StockFixtures;

use function Pest\Laravel\seed;

/*
| "A stock row holds one item in one place": a unique key on each stock table keeps it, and
| StockLedger::lot() relies on it. SELECT ... FOR UPDATE locks nothing while the row does not exist
| yet, so two first receipts of an item (or two transfers into a pharmacy that never held the drug)
| each inserted a row of their own.
*/

it('refuses a second row of an item in a place, on every stock table with one row per item', function (): void {
    seed(WarehouseSeeder::class);
    $medication = Medication::factory()->create();
    $warehouse = StockFixtures::departmentWarehouse();
    $now = now();
    $rows = [
        'pharmacy_medications' => ['pharmacy_id' => Pharmacy::factory()->create()->id, 'medication_id' => $medication->id],
        'warehouse_medical_supplies' => ['warehouse_id' => $warehouse->id, 'supply_id' => MedicalSupply::factory()->create()->id],
        'warehouse_household_materials' => ['warehouse_id' => $warehouse->id, 'household_material_id' => HouseholdMaterial::factory()->create()->id],
        'main_warehouse_medical_supplies' => ['main_warehouse_id' => MainWarehouse::MAIN, 'supply_id' => MedicalSupply::factory()->create()->id],
        'main_warehouse_household_materials' => ['main_warehouse_id' => MainWarehouse::MAIN, 'household_material_id' => HouseholdMaterial::factory()->create()->id],
        // A batch without a number is one batch too (NULLS NOT DISTINCT).
        'warehouse_accountings' => ['warehouse_id' => $warehouse->id, 'medication_id' => $medication->id, 'batch_number' => null, 'expiration_date' => StockFixtures::monthsAhead(6)],
    ];

    foreach ($rows as $table => $key) {
        DB::table($table)->insert([...$key, 'quantity' => 5, 'created_at' => $now, 'updated_at' => $now]);

        // A savepoint of its own: the refused insert must not abort the test's transaction.
        expect(fn () => DB::transaction(fn () => DB::table($table)->insert([...$key, 'quantity' => 7, 'created_at' => $now, 'updated_at' => $now])))
            ->toThrow(UniqueConstraintViolationException::class);
    }

    // Department batches of the same drug differ by their number or expiry.
    DB::table('warehouse_accountings')->insert([...$rows['warehouse_accountings'], 'batch_number' => 'B-2', 'quantity' => 1, 'created_at' => $now, 'updated_at' => $now]);

    expect(DB::table('warehouse_accountings')->where('warehouse_id', $warehouse->id)->count())->toBe(2);
});

it('merges the rows written twice before the key into the oldest one, with their movements', function (): void {
    seed(WarehouseSeeder::class);
    $migration = require database_path('migrations/2025_11_21_110000_add_unique_keys_to_stock_rows.php');
    $migration->down();

    $warehouse = StockFixtures::departmentWarehouse();
    $medication = Medication::factory()->create();
    $batch = ['warehouse_id' => $warehouse->id, 'medication_id' => $medication->id, 'batch_number' => null, 'expiration_date' => StockFixtures::monthsAhead(6)];
    $older = WarehouseAccounting::factory()->create([...$batch, 'quantity' => 3, 'date_of_receipt' => '2025-11-10']);
    $copy = WarehouseAccounting::factory()->create([...$batch, 'quantity' => 4, 'date_of_receipt' => '2025-11-05']);
    $other = WarehouseAccounting::factory()->create([...$batch, 'batch_number' => 'B-2', 'quantity' => 9]);
    $supply = MedicalSupply::factory()->create();
    MainWarehouseMedicalSupply::query()->insert([
        ['main_warehouse_id' => MainWarehouse::MAIN, 'supply_id' => $supply->id, 'quantity' => 5],
        ['main_warehouse_id' => MainWarehouse::MAIN, 'supply_id' => $supply->id, 'quantity' => 7],
    ]);
    // What a prescription took from the copy: its return must find the merged batch.
    $movement = StockMovement::query()->create([
        'stock_type' => 'warehouse_accounting', 'stock_id' => $copy->id, 'item_type' => 'medication', 'item_id' => $medication->id,
        'quantity' => -2, 'reason' => StockMovementReason::Dispense,
    ]);

    $migration->up();

    expect(WarehouseAccounting::query()->where('warehouse_id', $warehouse->id)->orderBy('id')->get(['id', 'quantity', 'date_of_receipt'])
        ->map(fn (WarehouseAccounting $row): array => [$row->id, $row->quantity, $row->date_of_receipt?->toDateString()])->all())
        ->toBe([[$older->id, 7, '2025-11-05'], [$other->id, 9, $other->date_of_receipt?->toDateString()]])
        ->and($movement->fresh()->stock_id)->toBe($older->id)
        ->and(MainWarehouseMedicalSupply::query()->where('supply_id', $supply->id)->pluck('quantity')->all())->toBe([12]);
});

it('makes the second of two concurrent first puts wait for the first one and then use its row', function (): void {
    // Two sessions of their own: the test's own connection runs inside the transaction
    // RefreshDatabase rolls back, which another session cannot see into.
    $default = config('database.default');
    config([
        'database.connections.race_a' => config("database.connections.{$default}"),
        'database.connections.race_b' => config("database.connections.{$default}"),
    ]);
    $a = DB::connection('race_a');
    $b = DB::connection('race_b');
    $ledger = app(StockLedger::class);
    $now = now();
    $warehouseId = $a->table('main_warehouses')->insertGetId(['name' => 'Склад проверки блокировок', 'created_at' => $now, 'updated_at' => $now]);
    $supplyId = $a->table('medical_supplies')->insertGetId(['name' => 'Шприц 5 мл', 'created_at' => $now, 'updated_at' => $now]);
    $key = ['main_warehouse_id' => $warehouseId, 'supply_id' => $supplyId];

    try {
        $a->beginTransaction();
        $first = $ledger->lot(MainWarehouseMedicalSupply::on('race_a'), $key);

        // B must wait for A's row instead of inserting one of its own: it gives up after 300 ms.
        $b->beginTransaction();
        $b->statement("SET LOCAL lock_timeout = '300ms'");
        expect(fn () => $ledger->lot(MainWarehouseMedicalSupply::on('race_b'), $key))
            ->toThrow(fn (QueryException $e): Expectation => expect($e->getCode())->toBe('55P03'));
        $b->rollBack();

        $a->commit();

        $b->beginTransaction();
        $second = $ledger->lot(MainWarehouseMedicalSupply::on('race_b'), $key);
        $b->commit();

        expect($first->exists)->toBeTrue()
            ->and($second->getKey())->toBe($first->getKey())
            ->and($a->table('main_warehouse_medical_supplies')->where($key)->count())->toBe(1);
    } finally {
        foreach ([$a, $b] as $connection) {
            while ($connection->transactionLevel() > 0) {
                $connection->rollBack();
            }
        }

        // The stock rows go with the warehouse and the supply (ON DELETE CASCADE).
        $a->table('main_warehouses')->delete($warehouseId);
        $a->table('medical_supplies')->delete($supplyId);
        DB::purge('race_a');
        DB::purge('race_b');
    }
});
