<?php

namespace Database\Factories;

use App\Enums\SupplyRequestStatus;
use App\Models\MainWarehouse;
use App\Models\SupplyRequest;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A pending supply request of a department warehouse (without items). It points at the seeded
 * main warehouse, which the test or seeder has to create first (WarehouseSeeder).
 *
 * @extends Factory<SupplyRequest>
 */
class SupplyRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'main_warehouse_id' => MainWarehouse::MAIN,
            'status' => SupplyRequestStatus::Pending,
        ];
    }
}
