<?php

use App\Enums\SupplyRequestStatus;
use App\Models\HouseholdMaterial;
use App\Models\MainWarehouse;
use App\Models\MainWarehouseHouseholdMaterial;
use App\Models\MainWarehouseMedicalSupply;
use App\Models\MedicalSupply;
use App\Models\Medication;
use App\Models\SupplyRequest;
use App\Models\SupplyRequestItem;
use App\Models\Warehouse;
use App\Models\WarehouseAccounting;
use App\Models\WarehouseHouseholdMaterial;
use App\Models\WarehouseMedicalSupply;
use App\Models\WarehouseType;
use Database\Seeders\WarehouseSeeder;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Stock\StockFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;

beforeEach(function (): void {
    seed(WarehouseSeeder::class);
});

function stockSupplyRequestFor(Warehouse $warehouse, array $items): SupplyRequest
{
    $request = SupplyRequest::factory()->create(['warehouse_id' => $warehouse->id]);

    foreach ($items as [$item, $quantity]) {
        $request->items()->create([
            'supplyable_type' => $item->getMorphClass(),
            'supplyable_id' => $item->getKey(),
            'requested_quantity' => $quantity,
        ]);
    }

    return $request;
}

function stockApprove(SupplyRequest $request): TestResponse
{
    return post(route('supply-requests.approve', $request));
}

it('moves exactly the requested quantity, earliest expiry first, without touching the other batches', function (): void {
    // Regression: every batch of the drug used to be overwritten with "first batch - requested".
    $medication = Medication::factory()->create();
    $later = StockFixtures::mainBatch($medication, 500, StockFixtures::monthsAhead(14), ['batch_number' => 'LATE']);
    $sooner = StockFixtures::mainBatch($medication, 10, StockFixtures::monthsAhead(2), ['batch_number' => 'SOON']);
    $warehouse = StockFixtures::departmentWarehouse();
    $request = stockSupplyRequestFor($warehouse, [[$medication, 30]]);
    $approver = adminWithPermissions('approve_supply_requests');

    actingAs($approver, 'admin');

    stockApprove($request)->assertRedirect()->assertSessionHas('success');

    expect($sooner->fresh()->quantity)->toBe(0)
        ->and($later->fresh()->quantity)->toBe(480)
        ->and(WarehouseAccounting::query()->whereBelongsTo($warehouse)->orderBy('expiration_date')->get()
            ->map(fn (WarehouseAccounting $lot): array => [$lot->batch_number, $lot->expiration_date->toDateString(), $lot->quantity])->all())
        ->toBe([['SOON', StockFixtures::monthsAhead(2), 10], ['LATE', StockFixtures::monthsAhead(14), 20]])
        ->and($request->fresh()->status)->toBe(SupplyRequestStatus::Approved)
        ->and($request->items()->value('approved_quantity'))->toBe(30)
        ->and(StockFixtures::movementsOf($sooner))->toBe([['supply_request', -10]])
        ->and(StockFixtures::movementsOf($later))->toBe([['supply_request', -20]])
        ->and(StockFixtures::movementAuthors())->toBe([$approver->id]);
});

it('refuses to approve a request twice, so the stock moves once', function (): void {
    // Regression: a repeated POST moved everything a second time.
    $medication = Medication::factory()->create();
    $batch = StockFixtures::mainBatch($medication, 100, StockFixtures::monthsAhead(7));
    $warehouse = StockFixtures::departmentWarehouse();
    $request = stockSupplyRequestFor($warehouse, [[$medication, 40]]);

    actingAs(adminWithPermissions('approve_supply_requests'), 'admin');

    stockApprove($request)->assertSessionHas('success');
    stockApprove($request)->assertSessionHas('error', 'Заказ в статусе «Принят» нельзя перевести в статус «Принят».');

    expect($batch->fresh()->quantity)->toBe(60)
        ->and(WarehouseAccounting::query()->whereBelongsTo($warehouse)->sum('quantity'))->toBe(40);
});

it('moves nothing when one item is short and keeps the request pending', function (): void {
    // Regression: the items before the short one were committed and moved again on the next approval.
    $enough = Medication::factory()->create();
    $short = Medication::factory()->create(['name' => 'Цефтриаксон']);
    $enoughBatch = StockFixtures::mainBatch($enough, 100, StockFixtures::monthsAhead(7));
    StockFixtures::mainBatch($short, 5, StockFixtures::monthsAhead(7));
    $warehouse = StockFixtures::departmentWarehouse();
    $request = stockSupplyRequestFor($warehouse, [[$enough, 10], [$short, 50]]);

    actingAs(adminWithPermissions('approve_supply_requests'), 'admin');

    stockApprove($request)->assertSessionHas('error', 'Недостаточно «Цефтриаксон» (главный склад): нужно 50, в наличии 5.');

    expect($enoughBatch->fresh()->quantity)->toBe(100)
        ->and(WarehouseAccounting::query()->whereBelongsTo($warehouse)->exists())->toBeFalse()
        ->and($request->fresh()->status)->toBe(SupplyRequestStatus::Pending)
        ->and(SupplyRequestItem::query()->whereNotNull('approved_quantity')->exists())->toBeFalse();
});

it('moves medical supplies and household materials into the department rows', function (): void {
    $supply = MedicalSupply::factory()->create();
    $material = HouseholdMaterial::factory()->create();
    $mainSupply = MainWarehouseMedicalSupply::query()->create(['main_warehouse_id' => MainWarehouse::MAIN, 'supply_id' => $supply->id, 'quantity' => 50]);
    $mainMaterial = MainWarehouseHouseholdMaterial::query()->create(['main_warehouse_id' => MainWarehouse::MAIN, 'household_material_id' => $material->id, 'quantity' => 8]);

    $supplyWarehouse = StockFixtures::departmentWarehouse(WarehouseType::MEDICAL_SUPPLIES);
    WarehouseMedicalSupply::query()->create(['warehouse_id' => $supplyWarehouse->id, 'supply_id' => $supply->id, 'quantity' => 3]);
    $householdWarehouse = StockFixtures::departmentWarehouse(WarehouseType::HOUSEHOLD_MATERIALS);

    actingAs(adminWithPermissions('approve_supply_requests'), 'admin');

    stockApprove(stockSupplyRequestFor($supplyWarehouse, [[$supply, 20]]))->assertSessionHas('success');
    stockApprove(stockSupplyRequestFor($householdWarehouse, [[$material, 8]]))->assertSessionHas('success');

    expect($mainSupply->fresh()->quantity)->toBe(30)
        ->and(WarehouseMedicalSupply::query()->whereBelongsTo($supplyWarehouse)->sole()->quantity)->toBe(23)
        ->and($mainMaterial->fresh()->quantity)->toBe(0)
        ->and(WarehouseHouseholdMaterial::query()->whereBelongsTo($householdWarehouse)->sole()->quantity)->toBe(8);
});

it('requires the approve permission', function (): void {
    $medication = Medication::factory()->create();
    $batch = StockFixtures::mainBatch($medication, 100, StockFixtures::monthsAhead(7));
    $request = stockSupplyRequestFor(StockFixtures::departmentWarehouse(), [[$medication, 10]]);

    actingAs(adminWithPermissions('main_warehouse_remain'), 'admin');

    stockApprove($request)->assertForbidden();

    expect($batch->fresh()->quantity)->toBe(100);
});

it('lists the requests with their items for the main warehouse', function (): void {
    $medication = Medication::factory()->create(['name' => 'Гепарин']);
    stockSupplyRequestFor(StockFixtures::departmentWarehouse(), [[$medication, 10]]);

    actingAs(adminWithPermissions('main_warehouse_remain', 'approve_supply_requests'), 'admin')
        ->get(route('supply-requests.index'))
        ->assertOk()
        ->assertSeeText('Гепарин')
        ->assertSeeText('Принять заказ');
});

it('creates a request with the items of the warehouse type', function (): void {
    // Regression: the request and its items were written without a transaction or a type check.
    $warehouse = StockFixtures::departmentWarehouse(WarehouseType::MEDICAL_SUPPLIES);
    $supply = MedicalSupply::factory()->create();

    actingAs(adminWithPermissions('create_supply_requests'), 'admin');

    post(route('supply-requests.store'), [
        'warehouse_id' => $warehouse->id,
        'items' => [['id' => $supply->id, 'quantity' => 7]],
    ])->assertRedirect(route('warehouse.remains'))->assertSessionHas('success');

    $request = SupplyRequest::query()->sole();

    expect($request->main_warehouse_id)->toBe(MainWarehouse::MAIN)
        ->and($request->status)->toBe(SupplyRequestStatus::Pending)
        ->and($request->items()->sole()->only(['supplyable_type', 'supplyable_id', 'requested_quantity']))
        ->toBe(['supplyable_type' => 'medical_supply', 'supplyable_id' => $supply->id, 'requested_quantity' => 7]);
});

it('refuses an item the warehouse type does not store', function (): void {
    $warehouse = StockFixtures::departmentWarehouse(WarehouseType::MEDICAL_SUPPLIES);
    $medication = Medication::factory()->create();

    actingAs(adminWithPermissions('create_supply_requests'), 'admin');

    post(route('supply-requests.store'), [
        'warehouse_id' => $warehouse->id,
        // an existing medication, while no medical supply has its id
        'items' => [['id' => $medication->id, 'quantity' => 7]],
    ])->assertSessionHasErrors('items.0.id');

    expect(SupplyRequest::query()->exists())->toBeFalse();
});

it('never sends an expired batch to a department, however early it expires', function (): void {
    // Earliest expiry first used to pick the batch that had already expired.
    $medication = Medication::factory()->create(['name' => 'Окситоцин']);
    $expired = StockFixtures::mainBatch($medication, 100, today()->subDay()->toDateString());
    $usable = StockFixtures::mainBatch($medication, 30, StockFixtures::monthsAhead(3));
    $warehouse = StockFixtures::departmentWarehouse();

    actingAs(adminWithPermissions('approve_supply_requests'), 'admin');

    stockApprove(stockSupplyRequestFor($warehouse, [[$medication, 50]]))
        ->assertSessionHas('error', 'Недостаточно «Окситоцин» (главный склад): нужно 50, в наличии 30.');
    stockApprove(stockSupplyRequestFor($warehouse, [[$medication, 20]]))->assertSessionHas('success');

    expect($expired->fresh()->quantity)->toBe(100)
        ->and($usable->fresh()->quantity)->toBe(10)
        ->and(WarehouseAccounting::query()->whereBelongsTo($warehouse)->sole()->expiration_date->toDateString())
        ->toBe($usable->expiration_date->toDateString());
});

it('refuses a requested quantity the stock columns cannot hold', function (): void {
    $warehouse = StockFixtures::departmentWarehouse();
    $medication = Medication::factory()->create();

    actingAs(adminWithPermissions('create_supply_requests'), 'admin');

    post(route('supply-requests.store'), [
        'warehouse_id' => $warehouse->id,
        'items' => [['id' => $medication->id, 'quantity' => 3_000_000_000]],
    ])->assertSessionHasErrors('items.0.quantity');

    expect(SupplyRequest::query()->exists())->toBeFalse();
});

it('requires create_supply_requests to send a request', function (): void {
    $warehouse = StockFixtures::departmentWarehouse();

    actingAs(adminWithPermissions('view_warehouse_remains'), 'admin');

    post(route('supply-requests.store'), [
        'warehouse_id' => $warehouse->id,
        'items' => [['id' => Medication::factory()->create()->id, 'quantity' => 1]],
    ])->assertForbidden();

    expect(SupplyRequest::query()->exists())->toBeFalse();
});

function stockReject(SupplyRequest $request, ?string $reason = null): TestResponse
{
    return post(route('supply-requests.reject', $request), ['reason' => $reason]);
}

it('rejects a pending request with the reason and moves nothing', function (): void {
    $medication = Medication::factory()->create();
    $batch = StockFixtures::mainBatch($medication, 100, StockFixtures::monthsAhead(7));
    $request = stockSupplyRequestFor(StockFixtures::departmentWarehouse(), [[$medication, 40]]);

    actingAs(adminWithPermissions('approve_supply_requests'), 'admin');

    stockReject($request, '  Поставка ожидается в декабре  ')
        ->assertRedirect()
        ->assertSessionHas('success', 'Заказ отклонён: товары не перемещались.');

    expect($request->fresh())
        ->status->toBe(SupplyRequestStatus::Rejected)
        ->rejection_reason->toBe('Поставка ожидается в декабре')
        ->and($batch->fresh()->quantity)->toBe(100)
        ->and(StockFixtures::movementsOf($batch))->toBe([])
        ->and($request->items()->value('approved_quantity'))->toBeNull();
});

it('rejects without a reason', function (): void {
    $request = stockSupplyRequestFor(StockFixtures::departmentWarehouse(), [[Medication::factory()->create(), 5]]);

    actingAs(adminWithPermissions('approve_supply_requests'), 'admin');

    stockReject($request, '   ')->assertSessionHas('success');

    expect($request->fresh())
        ->status->toBe(SupplyRequestStatus::Rejected)
        ->rejection_reason->toBeNull();
});

it('never approves a rejected request, so its stock stays in the main warehouse', function (): void {
    $medication = Medication::factory()->create();
    $batch = StockFixtures::mainBatch($medication, 100, StockFixtures::monthsAhead(7));
    $request = stockSupplyRequestFor(StockFixtures::departmentWarehouse(), [[$medication, 40]]);

    actingAs(adminWithPermissions('approve_supply_requests'), 'admin');

    stockReject($request)->assertSessionHas('success');
    stockApprove($request)->assertSessionHas('error', 'Заказ в статусе «Отклонён» нельзя перевести в статус «Принят».');

    expect($request->fresh()->status)->toBe(SupplyRequestStatus::Rejected)
        ->and($batch->fresh()->quantity)->toBe(100)
        ->and(WarehouseAccounting::query()->exists())->toBeFalse();
});

it('decides a request once: no second rejection, no rejection after the approval', function (): void {
    $medication = Medication::factory()->create();
    StockFixtures::mainBatch($medication, 100, StockFixtures::monthsAhead(7));
    $warehouse = StockFixtures::departmentWarehouse();
    $rejected = stockSupplyRequestFor($warehouse, [[$medication, 10]]);
    $approved = stockSupplyRequestFor($warehouse, [[$medication, 10]]);

    actingAs(adminWithPermissions('approve_supply_requests'), 'admin');

    stockReject($rejected, 'Дубликат заказа')->assertSessionHas('success');
    stockReject($rejected, 'Другая причина')->assertSessionHas('error', 'Заказ в статусе «Отклонён» нельзя перевести в статус «Отклонён».');
    stockApprove($approved)->assertSessionHas('success');
    stockReject($approved, 'Поздно')->assertSessionHas('error', 'Заказ в статусе «Принят» нельзя перевести в статус «Отклонён».');

    expect($rejected->fresh()->rejection_reason)->toBe('Дубликат заказа')
        ->and($approved->fresh())
        ->status->toBe(SupplyRequestStatus::Approved)
        ->rejection_reason->toBeNull();
});

it('refuses a rejection without the approve permission', function (): void {
    $request = stockSupplyRequestFor(StockFixtures::departmentWarehouse(), [[Medication::factory()->create(), 5]]);

    actingAs(adminWithPermissions('main_warehouse_remain'), 'admin');

    stockReject($request, 'Нет')->assertForbidden();

    expect($request->fresh())
        ->status->toBe(SupplyRequestStatus::Pending)
        ->rejection_reason->toBeNull();
});

it('refuses a reason longer than the orders page shows', function (): void {
    $request = stockSupplyRequestFor(StockFixtures::departmentWarehouse(), [[Medication::factory()->create(), 5]]);

    actingAs(adminWithPermissions('approve_supply_requests'), 'admin');

    stockReject($request, str_repeat('а', 1001))->assertSessionHasErrors('reason');

    expect($request->fresh()->status)->toBe(SupplyRequestStatus::Pending);
});

it('offers the decision only on undecided requests and shows why a request was rejected', function (): void {
    $warehouse = StockFixtures::departmentWarehouse();
    $pending = stockSupplyRequestFor($warehouse, [[Medication::factory()->create(), 5]]);
    $rejected = stockSupplyRequestFor($warehouse, [[Medication::factory()->create(), 5]]);
    $rejected->update(['status' => SupplyRequestStatus::Rejected, 'rejection_reason' => 'Нет поставщика']);

    actingAs(adminWithPermissions('main_warehouse_remain', 'approve_supply_requests'), 'admin')
        ->get(route('supply-requests.index'))
        ->assertOk()
        ->assertSeeHtml(route('supply-requests.reject', $pending))
        ->assertDontSeeHtml(route('supply-requests.reject', $rejected))
        ->assertSeeText('Причина отказа: Нет поставщика')
        ->assertSeeHtml('<span class="badge bg-secondary-lt ms-2">Отклонён</span>');

    actingAs(adminWithPermissions('main_warehouse_remain'), 'admin')
        ->get(route('supply-requests.index'))
        ->assertOk()
        ->assertDontSeeHtml(route('supply-requests.reject', $pending));
});
