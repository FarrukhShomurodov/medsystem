<?php

use App\Enums\SupplyRequestStatus;
use App\Models\Medication;
use App\Models\SupplyRequest;
use App\Models\Warehouse;
use App\Models\WarehouseType;
use Database\Seeders\WarehouseSeeder;
use Tests\Feature\Stock\StockFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

/*
| The type of a department warehouse decides what it orders, stores, transfers and dispenses
| (WarehouseType::STOCK_ITEMS). Changing it under stock would strand the batches: they could no
| longer be transferred or ordered against, and inpatient prescriptions would silently switch to
| the main warehouse.
*/

beforeEach(function (): void {
    seed(WarehouseSeeder::class);
    $this->editor = adminWithPermissions('edit_warehouses');
});

/**
 * What the warehouse form posts.
 *
 * @return array<string, mixed>
 */
function warehouseForm(Warehouse $warehouse, int|string $type): array
{
    return [
        'name' => $warehouse->name,
        'description' => 'Склад отделения',
        'warehouse_type_id' => $type,
        'address' => 'Корпус 2',
        'total_volume' => 120,
        'occupied_volume' => 30,
        'occupied_volume_percentage' => 25,
        'max_capacity' => 500,
        'phone' => '+998 71 200-00-00',
        'email' => 'ward@example.test',
        'work_time' => '08:00–17:00',
        'head_name' => 'Каримов А.',
        'head_position' => 'Заведующий',
        'head_contact' => '+998 90 000-00-00',
        'temperature' => '15–25°C',
        'humidity' => '60%',
        'light_protected' => 'Да',
        'is_dangerous' => false,
        'refrigeration' => 'Нет',
        'freezing' => 'Нет',
        'flammable' => 'Нет',
        'shelf_location' => 'A-1',
        'special_zone' => 'Нет',
        'label' => 'Общий',
    ];
}

it('changes the type of a warehouse that never held stock', function (): void {
    $warehouse = StockFixtures::departmentWarehouse();

    actingAs($this->editor, 'admin')
        ->put(route('warehouses.update', $warehouse), warehouseForm($warehouse, WarehouseType::MEDICAL_SUPPLIES))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('warehouses.index'));

    expect($warehouse->fresh()->warehouse_type_id)->toBe(WarehouseType::MEDICAL_SUPPLIES);
});

it('refuses to change the type of a warehouse that holds stock or held it', function (int $quantity): void {
    // A batch at zero is still refilled when a dispensed prescription is returned to it.
    $warehouse = StockFixtures::departmentWarehouse();
    $batch = StockFixtures::departmentBatch($warehouse, Medication::factory()->create(), $quantity, StockFixtures::monthsAhead(6));

    actingAs($this->editor, 'admin')
        ->from(route('warehouses.edit', $warehouse))
        ->put(route('warehouses.update', $warehouse), [...warehouseForm($warehouse, WarehouseType::MEDICAL_SUPPLIES), 'name' => 'Новое имя'])
        ->assertRedirect(route('warehouses.edit', $warehouse))
        ->assertSessionHasErrors(['warehouse_type_id' => 'Тип склада нельзя изменить: на складе есть или были остатки, либо есть незавершённые заказы и переводы.']);

    expect($warehouse->fresh())
        ->warehouse_type_id->toBe(WarehouseType::MEDICATIONS)
        ->name->not->toBe('Новое имя')
        ->and($batch->fresh()->quantity)->toBe($quantity);
})->with(['a batch in stock' => 10, 'a batch used up' => 0]);

it('refuses to change the type of a warehouse with a pending supply request', function (): void {
    $warehouse = StockFixtures::departmentWarehouse();
    SupplyRequest::factory()->create(['warehouse_id' => $warehouse->id, 'status' => SupplyRequestStatus::Pending]);

    actingAs($this->editor, 'admin')
        ->put(route('warehouses.update', $warehouse), warehouseForm($warehouse, WarehouseType::HOUSEHOLD_MATERIALS))
        ->assertSessionHasErrors('warehouse_type_id');

    expect($warehouse->fresh()->warehouse_type_id)->toBe(WarehouseType::MEDICATIONS);
});

it('keeps the type of a warehouse with stock editable to itself, so the other fields still save', function (): void {
    $warehouse = StockFixtures::departmentWarehouse();
    StockFixtures::departmentBatch($warehouse, Medication::factory()->create(), 10, StockFixtures::monthsAhead(6));

    actingAs($this->editor, 'admin')
        ->put(route('warehouses.update', $warehouse), [...warehouseForm($warehouse, WarehouseType::MEDICATIONS), 'name' => 'Склад хирургии'])
        ->assertSessionHasNoErrors();

    expect($warehouse->fresh()->name)->toBe('Склад хирургии');
});

it('refuses an unknown warehouse type with a validation error, not a database error', function (): void {
    $warehouse = StockFixtures::departmentWarehouse();

    actingAs($this->editor, 'admin')
        ->put(route('warehouses.update', $warehouse), warehouseForm($warehouse, 999))
        ->assertSessionHasErrors('warehouse_type_id');

    expect($warehouse->fresh()->warehouse_type_id)->toBe(WarehouseType::MEDICATIONS);
});
