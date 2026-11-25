<?php

use App\Enums\StockMovementReason;
use App\Models\HouseholdMaterial;
use App\Models\MainWarehouse;
use App\Models\MainWarehouseHouseholdMaterial;
use App\Models\MainWarehouseMedicalSupply;
use App\Models\MainWarehouseMedication;
use App\Models\MedicalSupply;
use App\Models\Medication;
use App\Models\StockMovement;
use App\Models\Supplier;
use Database\Seeders\WarehouseSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Frontend\RenderedPage;
use Tests\Feature\Stock\StockFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;

beforeEach(function (): void {
    seed(WarehouseSeeder::class);
});

/**
 * @return array<string, mixed>
 */
function stockReceiptLine(Medication $medication, int $quantity, string $batchNumber, string $expires): array
{
    return [
        'id' => $medication->id,
        'quantity' => $quantity,
        'expiration_date' => $expires,
        'date_of_receipt' => today()->toDateString(),
        'batch_number' => $batchNumber,
        'packaging_type' => 'blister',
        'quantity_per_package' => 10,
        'package_size' => 1,
        'retail_price' => 1250.5,
        'wholesale_price' => 1000,
        'supplier_id' => Supplier::factory()->create()->id,
        'markup' => 25,
        'total_price' => 125050,
        'incoming_price_total' => 100000,
        'incoming_price_per_unit' => 1000,
        'incoming_sum' => 100000,
    ];
}

it('keeps two batches of one medication received together as two batches', function (): void {
    // Regression: the receipt was keyed by medication id, so the second batch overwrote the first.
    $medication = Medication::factory()->create();
    $storekeeper = adminWithPermissions('edit_main_warehouse');

    actingAs($storekeeper, 'admin');

    post(route('main.warehouse.receipts.goods.store'), [
        'medications' => [
            stockReceiptLine($medication, 100, 'A-1', StockFixtures::monthsAhead(4)),
            stockReceiptLine($medication, 40, 'B-2', StockFixtures::monthsAhead(16)),
        ],
    ])->assertRedirect(route('main.warehouse.remains'))->assertSessionHas('success');

    $batches = MainWarehouseMedication::query()->where('medication_id', $medication->id)->orderBy('batch_number')->get();

    expect($batches->map->only(['batch_number', 'quantity', 'main_warehouse_id'])->all())->toBe([
        ['batch_number' => 'A-1', 'quantity' => 100, 'main_warehouse_id' => MainWarehouse::MAIN],
        ['batch_number' => 'B-2', 'quantity' => 40, 'main_warehouse_id' => MainWarehouse::MAIN],
    ])
        ->and($batches->first()->retail_price)->toBe('1250.50')
        ->and(StockFixtures::movementsOf($batches->first()))->toBe([['receipt', 100]])
        ->and(StockFixtures::movementAuthors())->toBe([$storekeeper->id]);
});

it('adds received supplies and household materials to their rows', function (): void {
    $supply = MedicalSupply::factory()->create();
    $material = HouseholdMaterial::factory()->create();
    $row = MainWarehouseMedicalSupply::query()->create(['main_warehouse_id' => MainWarehouse::MAIN, 'supply_id' => $supply->id, 'quantity' => 5]);

    actingAs(adminWithPermissions('edit_main_warehouse'), 'admin');

    post(route('main.warehouse.receipts.goods.store'), [
        'supplies' => [['id' => $supply->id, 'quantity' => 15], ['id' => 'ID', 'quantity' => '']],
        'household' => [['id' => $material->id, 'quantity' => 3]],
    ])->assertSessionHas('success');

    expect($row->fresh()->quantity)->toBe(20)
        ->and(MainWarehouseMedicalSupply::query()->count())->toBe(1)
        ->and(MainWarehouseHouseholdMaterial::query()->sole()->quantity)->toBe(3);
});

it('refuses an expired batch and a receipt without goods', function (): void {
    $medication = Medication::factory()->create();

    actingAs(adminWithPermissions('edit_main_warehouse'), 'admin');

    post(route('main.warehouse.receipts.goods.store'), [
        'medications' => [stockReceiptLine($medication, 10, 'OLD', today()->subDay()->toDateString())],
    ])->assertSessionHasErrors('medications.0.expiration_date');

    post(route('main.warehouse.receipts.goods.store'), [])->assertSessionHasErrors('medications');

    expect(MainWarehouseMedication::query()->exists())->toBeFalse();
});

it('writes off exactly the selected batch and keeps a trace of it', function (): void {
    // Regression: the medication id was passed and every batch of the drug was detached.
    $medication = Medication::factory()->create(['name' => 'Диклофенак']);
    $expired = StockFixtures::mainBatch($medication, 12, today()->subMonth()->toDateString());
    $kept = StockFixtures::mainBatch($medication, 300, StockFixtures::monthsAhead(25));
    $storekeeper = adminWithPermissions('main_warehouse_remain', 'edit_main_warehouse');

    actingAs($storekeeper, 'admin');

    delete(route('main.warehouse.receipts.goods.discard', ['type' => 'medications', 'id' => $expired->id]))
        ->assertRedirect(route('main.warehouse.remains'))
        ->assertSessionHas('success', 'Списано со склада: «Диклофенак», 12 шт.');

    expect(MainWarehouseMedication::query()->whereKey($expired->id)->exists())->toBeFalse()
        ->and($kept->fresh()->quantity)->toBe(300)
        ->and(StockMovement::query()->sole()->only(['reason', 'quantity', 'item_id', 'admin_id']))
        ->toEqual(['reason' => StockMovementReason::WriteOff, 'quantity' => -12, 'item_id' => $medication->id, 'admin_id' => $storekeeper->id]);
});

it('writes off a supply row by its own id', function (): void {
    $supply = MedicalSupply::factory()->create();
    $row = MainWarehouseMedicalSupply::query()->create(['main_warehouse_id' => MainWarehouse::MAIN, 'supply_id' => $supply->id, 'quantity' => 9]);

    actingAs(adminWithPermissions('edit_main_warehouse'), 'admin');

    delete(route('main.warehouse.receipts.goods.discard', ['type' => 'supplies', 'id' => $row->id]))->assertSessionHas('success');
    delete(route('main.warehouse.receipts.goods.discard', ['type' => 'supplies', 'id' => $row->id]))->assertNotFound();

    expect(MainWarehouseMedicalSupply::query()->exists())->toBeFalse();
});

it('answers 404 to a write-off of an unknown section', function (): void {
    $batch = StockFixtures::mainBatch(Medication::factory()->create(), 10, StockFixtures::monthsAhead(2));

    actingAs(adminWithPermissions('edit_main_warehouse'), 'admin');

    delete("/main-warehouse/receipts-discard/devices/{$batch->id}")->assertNotFound();

    expect($batch->fresh())->not->toBeNull();
});

it('writes off only with edit_main_warehouse and only by DELETE', function (): void {
    $batch = StockFixtures::mainBatch(Medication::factory()->create(), 10, StockFixtures::monthsAhead(2));
    $url = route('main.warehouse.receipts.goods.discard', ['type' => 'medications', 'id' => $batch->id]);

    actingAs(adminWithPermissions('main_warehouse_remain'), 'admin');
    delete($url)->assertForbidden();

    actingAs(adminWithPermissions('edit_main_warehouse'), 'admin');
    get($url)->assertMethodNotAllowed();

    expect($batch->fresh())->not->toBeNull();
});

it('lists every batch on the remains page and opens the batch itself', function (): void {
    $medication = Medication::factory()->create(['name' => 'Омепразол']);
    StockFixtures::mainBatch($medication, 70, StockFixtures::monthsAhead(6), ['batch_number' => 'OM-1']);
    $second = StockFixtures::mainBatch($medication, 30, StockFixtures::monthsAhead(18), ['batch_number' => 'OM-2']);
    StockFixtures::mainBatch($medication, 0, StockFixtures::monthsAhead(19), ['batch_number' => 'OM-EMPTY']);

    actingAs(adminWithPermissions('main_warehouse_remain', 'edit_main_warehouse'), 'admin');

    get(route('main.warehouse.remains'))
        ->assertOk()
        ->assertSeeInOrder(['OM-1', 'OM-2'])
        ->assertDontSeeText('OM-EMPTY');

    get(route('main.warehouse.show.medication.remain', $second))
        ->assertOk()
        ->assertSeeText('Омепразол, партия OM-2');

    // The receipt form can take a second batch of a drug as a line of its own.
    get(route('main.warehouse.receipts.goods.index'))->assertOk()->assertSee('+ партия');
});

it('prints the prices of a batch in the Russian form, the price per unit with its four decimals', function (): void {
    // The remains page printed "51250.00", "25.00" for the markup and "41000.0000" per unit.
    $batch = StockFixtures::mainBatch(Medication::factory()->create(), 40, StockFixtures::monthsAhead(6), [
        'retail_price' => '51250.00', 'markup' => '25.00', 'incoming_price_per_unit' => '1234.5678', 'total_price' => '2050000.00',
    ]);

    actingAs(adminWithPermissions('main_warehouse_remain'), 'admin');

    $row = RenderedPage::tableRows((string) get(route('main.warehouse.remains'))->assertOk()->getContent(), '//main//table')[0];

    // tableRows() reads the non-breaking spaces as plain ones.
    expect($row['Розн. цена'])->toBe('51 250,00')
        ->and($row['Наценка'])->toBe('25 %')
        ->and($row['Цена вход. за ед.'])->toBe('1 234,5678')
        ->and($row['Итоговая цена'])->toBe('2 050 000,00');

    get(route('main.warehouse.show.medication.remain', $batch))
        ->assertOk()
        ->assertSeeText(["51\u{00A0}250,00", "1\u{00A0}234,5678"])
        ->assertDontSeeText('51250.00');
});

it('refuses a received quantity the stock columns cannot hold instead of failing', function (): void {
    $medication = Medication::factory()->create();
    $supply = MedicalSupply::factory()->create();

    actingAs(adminWithPermissions('edit_main_warehouse'), 'admin');

    post(route('main.warehouse.receipts.goods.store'), [
        'medications' => [stockReceiptLine($medication, 3_000_000_000, 'BIG', StockFixtures::monthsAhead(6))],
        'supplies' => [['id' => $supply->id, 'quantity' => 3_000_000_000]],
    ])->assertSessionHasErrors(['medications.0.quantity', 'supplies.0.quantity']);

    expect(MainWarehouseMedication::query()->exists())->toBeFalse();
});

it('requires edit_main_warehouse to receive goods', function (): void {
    $medication = Medication::factory()->create();

    actingAs(adminWithPermissions('main_warehouse_remain'), 'admin');

    post(route('main.warehouse.receipts.goods.store'), [
        'medications' => [stockReceiptLine($medication, 10, 'NOPE', StockFixtures::monthsAhead(6))],
    ])->assertForbidden();

    expect(MainWarehouseMedication::query()->exists())->toBeFalse();
});

it('drops the stale stock column of the batches and restores it from the quantity on rollback', function (): void {
    // Nothing kept main_warehouse_medications.stock since StockLedger moves the stock: it only misled readers.
    expect(Schema::hasColumn('main_warehouse_medications', 'stock'))->toBeFalse();

    $batch = MainWarehouseMedication::factory()->create(['quantity' => 40]);
    $migration = require database_path('migrations/2025_11_20_100000_drop_stock_from_main_warehouse_medications_table.php');

    $migration->down();

    expect(DB::table('main_warehouse_medications')->where('id', $batch->id)->value('stock'))->toBe(40);

    $migration->up();

    expect(Schema::hasColumn('main_warehouse_medications', 'stock'))->toBeFalse()
        ->and($batch->fresh()->quantity)->toBe(40);
});
