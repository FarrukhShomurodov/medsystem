<?php

use App\Models\HouseholdMaterial;
use App\Models\MainWarehouse;
use App\Models\MainWarehouseHouseholdMaterial;
use App\Models\MainWarehouseMedicalSupply;
use App\Models\MedicalSupply;
use App\Models\Medication;
use App\Models\Pharmacy;
use App\Models\PharmacyMedication;
use App\Models\SupplyRequest;
use App\Models\TransferMedication;
use App\Models\WarehouseHouseholdMaterial;
use App\Models\WarehouseMedicalSupply;
use App\Models\WarehouseType;
use Database\Seeders\WarehouseSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Pest\Expectation;
use Tests\Feature\Stock\StockFixtures;

use function Pest\Laravel\seed;

/*
| The CHECK constraints of the stock tables (migration 2025_11_19_100200): the last guard against
| negative stock and empty order lines for writes that bypass StockLedger. They are added NOT VALID,
| so they hold for every new or changed row.
*/

beforeEach(function (): void {
    seed(WarehouseSeeder::class);
});

/** table => [the constraint, the column it guards, the first value it refuses, a row of the table] */
dataset('stock quantity checks', fn (): array => [
    'main warehouse batches' => ['main_warehouse_medications', 'quantity_not_negative', 'quantity', -1,
        fn (): Model => StockFixtures::mainBatch(Medication::factory()->create(), 5, StockFixtures::monthsAhead(3))],
    'main warehouse supplies' => ['main_warehouse_medical_supplies', 'quantity_not_negative', 'quantity', -1,
        fn (): Model => MainWarehouseMedicalSupply::query()->create(['main_warehouse_id' => MainWarehouse::MAIN, 'supply_id' => MedicalSupply::factory()->create()->id, 'quantity' => 5])],
    'main warehouse household materials' => ['main_warehouse_household_materials', 'quantity_not_negative', 'quantity', -1,
        fn (): Model => MainWarehouseHouseholdMaterial::query()->create(['main_warehouse_id' => MainWarehouse::MAIN, 'household_material_id' => HouseholdMaterial::factory()->create()->id, 'quantity' => 5])],
    'department batches' => ['warehouse_accountings', 'quantity_not_negative', 'quantity', -1,
        fn (): Model => StockFixtures::departmentBatch(StockFixtures::departmentWarehouse(), Medication::factory()->create(), 5, StockFixtures::monthsAhead(3))],
    'department supplies' => ['warehouse_medical_supplies', 'quantity_not_negative', 'quantity', -1,
        fn (): Model => WarehouseMedicalSupply::query()->create(['warehouse_id' => StockFixtures::departmentWarehouse(WarehouseType::MEDICAL_SUPPLIES)->id, 'supply_id' => MedicalSupply::factory()->create()->id, 'quantity' => 5])],
    'department household materials' => ['warehouse_household_materials', 'quantity_not_negative', 'quantity', -1,
        fn (): Model => WarehouseHouseholdMaterial::query()->create(['warehouse_id' => StockFixtures::departmentWarehouse(WarehouseType::HOUSEHOLD_MATERIALS)->id, 'household_material_id' => HouseholdMaterial::factory()->create()->id, 'quantity' => 5])],
    'pharmacy stock' => ['pharmacy_medications', 'quantity_not_negative', 'quantity', -1,
        fn (): Model => PharmacyMedication::query()->create(['pharmacy_id' => Pharmacy::factory()->create()->id, 'medication_id' => Medication::factory()->create()->id, 'quantity' => 5])],
    'transfer lines' => ['transfer_medications', 'quantity_positive', 'quantity', 0,
        fn (): Model => TransferMedication::factory()->create()],
    'supply request lines' => ['supply_request_items', 'requested_quantity_positive', 'requested_quantity', 0,
        fn (): Model => SupplyRequest::factory()->create()->items()->create(['supplyable_type' => (new Medication)->getMorphClass(), 'supplyable_id' => Medication::factory()->create()->id, 'requested_quantity' => 3])],
]);

it('keeps the CHECK constraint of every stock table', function (string $table, string $constraint, string $column, int $refused, Closure $row): void {
    // A migration that dropped or renamed one would remove the last guard without a failing test.
    expect(DB::scalar('select count(*) from pg_constraint where conname = ? and contype = ?', ["{$table}_{$constraint}", 'c']))->toBe(1);
})->with('stock quantity checks');

it('refuses a quantity below the limit written past StockLedger', function (string $table, string $constraint, string $column, int $refused, Closure $row): void {
    $id = $row()->getKey();

    // A failed statement aborts the PostgreSQL transaction; the savepoint keeps the test's one usable.
    DB::beginTransaction();

    expect(fn (): int => DB::table($table)->where('id', $id)->update([$column => $refused]))
        ->toThrow(fn (QueryException $e): Expectation => expect($e->getCode())->toBe('23514')
            ->and($e->getMessage())->toContain("{$table}_{$constraint}"));

    DB::rollBack();

    expect(DB::table($table)->where('id', $id)->value($column))->toBeGreaterThan(0);
})->with('stock quantity checks');
