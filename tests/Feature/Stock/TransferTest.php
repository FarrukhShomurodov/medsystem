<?php

use App\Enums\TransferStatus;
use App\Models\Medication;
use App\Models\Pharmacy;
use App\Models\PharmacyMedication;
use App\Models\Transfer;
use App\Models\TransferMedication;
use App\Models\WarehouseAccounting;
use App\Models\WarehouseType;
use Database\Seeders\WarehouseSeeder;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Stock\StockFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;

beforeEach(function (): void {
    seed(WarehouseSeeder::class);
});

/**
 * A pending transfer of $quantity units of a medication the warehouse holds in two batches.
 *
 * @return array{Transfer, WarehouseAccounting, WarehouseAccounting}
 */
function stockPendingTransfer(int $quantity, string $name = 'Амоксициллин'): array
{
    $medication = Medication::factory()->create(['name' => $name]);
    $warehouse = StockFixtures::departmentWarehouse();
    $later = StockFixtures::departmentBatch($warehouse, $medication, 50, StockFixtures::monthsAhead(14));
    $sooner = StockFixtures::departmentBatch($warehouse, $medication, 8, StockFixtures::monthsAhead(2));

    $transfer = Transfer::factory()->create(['from_warehouse_id' => $warehouse->id]);
    TransferMedication::factory()->create(['transfer_id' => $transfer->id, 'medication_id' => $medication->id, 'quantity' => $quantity]);

    return [$transfer, $sooner, $later];
}

function stockChangeTransferStatus(Transfer $transfer, string $status): TestResponse
{
    return patchJson(route('api.transfers.update-status', $transfer), ['status' => $status]);
}

it('moves the stock to the pharmacy when the transfer is completed, earliest expiry first', function (): void {
    [$transfer, $sooner, $later] = stockPendingTransfer(20);
    $pharmacist = adminWithPermissions('edit_transfers');

    actingAs($pharmacist, 'admin');

    stockChangeTransferStatus($transfer, 'completed')->assertOk()->assertJson(['success' => true, 'status' => 'completed']);

    expect($sooner->fresh()->quantity)->toBe(0)
        ->and($later->fresh()->quantity)->toBe(38)
        ->and(PharmacyMedication::query()->where('pharmacy_id', $transfer->to_pharmacy_id)->sole()->quantity)->toBe(20)
        ->and($transfer->fresh()->status)->toBe(TransferStatus::Completed)
        ->and(StockFixtures::movementAuthors())->toBe([$pharmacist->id]);
});

it('moves the stock only once when completed twice', function (): void {
    // Regression: a repeated "completed" decremented the warehouse again.
    [$transfer, $sooner, $later] = stockPendingTransfer(20);

    actingAs(adminWithPermissions('edit_transfers'), 'admin');

    stockChangeTransferStatus($transfer, 'completed')->assertOk();
    stockChangeTransferStatus($transfer, 'completed')
        ->assertUnprocessable()
        ->assertJson(['message' => 'Перевод в статусе «Завершён» нельзя перевести в статус «Завершён».']);

    expect($sooner->fresh()->quantity + $later->fresh()->quantity)->toBe(38)
        ->and(PharmacyMedication::query()->sole()->quantity)->toBe(20);
});

it('keeps a transfer the warehouse cannot cover pending, with nothing moved', function (): void {
    // Regression: the status was saved as completed before the stock check failed with a 500.
    [$transfer, $sooner, $later] = stockPendingTransfer(100, 'Гепарин');

    actingAs(adminWithPermissions('edit_transfers'), 'admin');

    stockChangeTransferStatus($transfer, 'completed')
        ->assertUnprocessable()
        ->assertJsonPath('message', fn (string $message): bool => str_contains($message, 'Недостаточно «Гепарин»') && str_contains($message, 'нужно 100, в наличии 58'));

    expect($transfer->fresh()->status)->toBe(TransferStatus::Pending)
        ->and($sooner->fresh()->quantity)->toBe(8)
        ->and($later->fresh()->quantity)->toBe(50)
        ->and(PharmacyMedication::query()->exists())->toBeFalse();
});

it('cancels a pending transfer without moving stock, and never completes it afterwards', function (): void {
    [$transfer, $sooner] = stockPendingTransfer(5);

    actingAs(adminWithPermissions('edit_transfers'), 'admin');

    stockChangeTransferStatus($transfer, 'canceled')->assertOk();
    stockChangeTransferStatus($transfer, 'completed')->assertUnprocessable();

    expect($transfer->fresh()->status)->toBe(TransferStatus::Canceled)
        ->and($sooner->fresh()->quantity)->toBe(8);
});

it('requires edit_transfers to change the status, list_transfers is not enough', function (): void {
    [$transfer, $sooner] = stockPendingTransfer(5);

    actingAs(adminWithPermissions('list_transfers'), 'admin');

    stockChangeTransferStatus($transfer, 'completed')->assertForbidden();

    expect($sooner->fresh()->quantity)->toBe(8);
});

it('edits and deletes a pending transfer', function (): void {
    [$transfer] = stockPendingTransfer(5);
    $other = Medication::factory()->create();
    $pharmacy = Pharmacy::factory()->create();

    actingAs(adminWithPermissions('list_transfers', 'edit_transfers', 'delete_transfers'), 'admin');

    get(route('transfers.edit', $transfer))->assertOk()->assertSeeHtml('name="medications[0][quantity]"');

    put(route('transfers.update', $transfer), [
        'from_warehouse_id' => $transfer->from_warehouse_id,
        'to_pharmacy_id' => $pharmacy->id,
        'notes' => 'Пополнение аптеки',
        'medications' => [['medication_id' => $other->id, 'quantity' => 3]],
    ])->assertRedirect(route('transfers.index'))->assertSessionHas('success');

    expect($transfer->fresh()->to_pharmacy_id)->toBe($pharmacy->id)
        ->and($transfer->medications()->get(['medication_id', 'quantity'])->toArray())
        ->toBe([['medication_id' => $other->id, 'quantity' => 3]]);

    delete(route('transfers.destroy', $transfer))->assertRedirect(route('transfers.index'))->assertSessionHas('success');

    expect(Transfer::query()->whereKey($transfer->id)->exists())->toBeFalse();
});

it('refuses to edit or delete a completed transfer', function (): void {
    [$transfer] = stockPendingTransfer(5);
    $transfer->update(['status' => TransferStatus::Completed->value]);

    actingAs(adminWithPermissions('list_transfers', 'edit_transfers', 'delete_transfers'), 'admin');

    get(route('transfers.edit', $transfer))->assertRedirect(route('transfers.index'))->assertSessionHas('error');
    delete(route('transfers.destroy', $transfer))->assertSessionHas('error');

    expect(Transfer::query()->whereKey($transfer->id)->exists())->toBeTrue();
});

it('requires the transfer permissions to edit and delete', function (): void {
    [$transfer] = stockPendingTransfer(5);

    actingAs(adminWithPermissions('list_transfers'), 'admin');

    get(route('transfers.edit', $transfer))->assertForbidden();
    delete(route('transfers.destroy', $transfer))->assertForbidden();
});

it('creates a pending transfer and shows the controls only for what the admin may do', function (): void {
    $warehouse = StockFixtures::departmentWarehouse();
    $pharmacy = Pharmacy::factory()->create();
    $medication = Medication::factory()->create();

    actingAs(adminWithPermissions('list_transfers', 'create_transfers'), 'admin');

    get(route('transfers.create'))->assertOk();

    post(route('transfers.store'), [
        'from_warehouse_id' => $warehouse->id,
        'to_pharmacy_id' => $pharmacy->id,
        'medications' => [['medication_id' => $medication->id, 'quantity' => 4]],
    ])->assertRedirect(route('transfers.index'))->assertSessionHas('success', 'Перевод создан и ожидает подтверждения.');

    expect(Transfer::query()->sole()->status)->toBe(TransferStatus::Pending);

    get(route('transfers.index'))
        ->assertOk()
        ->assertSeeText('В ожидании')
        ->assertDontSeeHtml('<select class="form-select form-select-sm transfer-status"')
        ->assertDontSee(route('transfers.edit', Transfer::query()->sole()))
        ->assertDontSeeText('Удалить');
});

it('never transfers an expired batch to the pharmacy', function (): void {
    $medication = Medication::factory()->create(['name' => 'Инсулин']);
    $warehouse = StockFixtures::departmentWarehouse();
    $expired = StockFixtures::departmentBatch($warehouse, $medication, 40, today()->subMonth()->toDateString());
    $usable = StockFixtures::departmentBatch($warehouse, $medication, 6, StockFixtures::monthsAhead(5));
    $transfer = Transfer::factory()->create(['from_warehouse_id' => $warehouse->id]);
    TransferMedication::factory()->create(['transfer_id' => $transfer->id, 'medication_id' => $medication->id, 'quantity' => 10]);

    actingAs(adminWithPermissions('edit_transfers'), 'admin');

    stockChangeTransferStatus($transfer, 'completed')
        ->assertUnprocessable()
        ->assertJsonPath('message', fn (string $message): bool => str_contains($message, 'нужно 10, в наличии 6'));

    expect($expired->fresh()->quantity)->toBe(40)
        ->and($usable->fresh()->quantity)->toBe(6)
        ->and($transfer->fresh()->status)->toBe(TransferStatus::Pending);
});

it('transfers only from a warehouse that stores medications', function (): void {
    $household = StockFixtures::departmentWarehouse(WarehouseType::HOUSEHOLD_MATERIALS);

    actingAs(adminWithPermissions('list_transfers', 'create_transfers'), 'admin');

    post(route('transfers.store'), [
        'from_warehouse_id' => $household->id,
        'to_pharmacy_id' => Pharmacy::factory()->create()->id,
        'medications' => [['medication_id' => Medication::factory()->create()->id, 'quantity' => 1]],
    ])->assertSessionHasErrors('from_warehouse_id');

    expect(Transfer::query()->exists())->toBeFalse();
});

it('requires create_transfers to create and edit_transfers to update a transfer', function (): void {
    [$transfer] = stockPendingTransfer(5);
    $form = [
        'from_warehouse_id' => $transfer->from_warehouse_id,
        'to_pharmacy_id' => $transfer->to_pharmacy_id,
        'medications' => [['medication_id' => Medication::factory()->create()->id, 'quantity' => 1]],
    ];

    actingAs(adminWithPermissions('list_transfers'), 'admin');

    post(route('transfers.store'), $form)->assertForbidden();
    put(route('transfers.update', $transfer), $form)->assertForbidden();

    expect(Transfer::query()->count())->toBe(1)
        ->and($transfer->medications()->sole()->quantity)->toBe(5);
});

it('refuses a status that is not one, naming the field in Russian', function (): void {
    [$transfer] = stockPendingTransfer(5);

    actingAs(adminWithPermissions('edit_transfers'), 'admin');

    stockChangeTransferStatus($transfer, 'archived')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status' => 'Выбранное значение для статус ошибочно.']);

    expect($transfer->fresh()->status)->toBe(TransferStatus::Pending);
});
