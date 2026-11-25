<?php

namespace Database\Factories;

use App\Models\Medication;
use App\Models\Warehouse;
use App\Models\WarehouseAccounting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A batch of a medication in a department warehouse.
 *
 * @extends Factory<WarehouseAccounting>
 */
class WarehouseAccountingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'medication_id' => Medication::factory(),
            'batch_number' => fake()->bothify('??-#####'),
            'expiration_date' => fake()->dateTimeBetween('+6 months', '+3 years')->format('Y-m-d'),
            'date_of_receipt' => fake()->dateTimeBetween('-1 month')->format('Y-m-d'),
            'quantity' => fake()->numberBetween(20, 200),
        ];
    }
}
