<?php

use App\Models\Pharmacy;
use App\Models\WarehouseCapacity;
use App\Models\WarehouseContact;
use App\Models\WarehouseStorageHandling;
use App\Services\PharmacyService;
use App\Services\WarehouseService;
use Database\Seeders\WarehouseSeeder;
use Tests\Feature\Stock\StockFixtures;

use function Pest\Laravel\seed;

beforeEach(function (): void {
    seed(WarehouseSeeder::class);
});

it('saves each section of a warehouse from its own fields of the form', function (): void {
    $warehouse = StockFixtures::departmentWarehouse();
    $form = [
        'name' => 'Склад хирургии',
        'phone' => '+998 71 200-00-00',
        'email' => 'surgery@example.test',
        'address' => 'Корпус 2',
        'work_time' => '08:00–17:00',
        'head_name' => 'Каримов А.',
        'head_position' => 'Заведующий',
        'head_contact' => '+998 90 000-00-00',
        'total_volume' => 120,
        'occupied_volume' => 30,
        'occupied_volume_percentage' => 25,
        'max_capacity' => 500,
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
    $service = app(WarehouseService::class);

    // The first save creates the sections the warehouse has no rows for yet.
    $service->update($warehouse, $form);
    $service->update($warehouse->fresh(), [...$form, 'phone' => '+998 71 300-00-00', 'max_capacity' => 600, 'label' => 'Холодовой']);

    expect($warehouse->fresh()?->name)->toBe('Склад хирургии')
        ->and(WarehouseContact::query()->where('warehouse_id', $warehouse->id)->sole()->only(['phone', 'head_name']))
        ->toBe(['phone' => '+998 71 300-00-00', 'head_name' => 'Каримов А.'])
        ->and(WarehouseCapacity::query()->where('warehouse_id', $warehouse->id)->sole()->only(['total_volume', 'max_capacity']))
        ->toBe(['total_volume' => 120, 'max_capacity' => 600])
        ->and(WarehouseStorageHandling::query()->where('warehouse_id', $warehouse->id)->sole()->only(['temperature', 'label']))
        ->toBe(['temperature' => '15–25°C', 'label' => 'Холодовой']);
});

it('saves the licence and the support service of a pharmacy, also one without them yet', function (): void {
    $pharmacy = Pharmacy::factory()->create();
    $form = [
        'name' => 'Аптека №1',
        'license_number' => 'L-100',
        'license_issue_date' => '2025-01-10',
        'license_expiry_date' => '2030-01-10',
        'consultation_phone' => '+998 71 111-11-11',
        'medication_consultation_available' => true,
    ];
    $service = app(PharmacyService::class);

    $service->update($pharmacy, $form);
    $service->update($pharmacy->fresh(), [...$form, 'license_number' => 'L-200', 'medication_consultation_available' => false]);

    $pharmacy = $pharmacy->fresh(['license', 'support']);

    expect($pharmacy?->name)->toBe('Аптека №1')
        ->and($pharmacy?->license()->count())->toBe(1)
        ->and($pharmacy?->license?->license_number)->toBe('L-200')
        ->and($pharmacy?->support?->consultation_phone)->toBe('+998 71 111-11-11')
        ->and($pharmacy?->support?->medication_consultation_available)->toBeFalse();
});
