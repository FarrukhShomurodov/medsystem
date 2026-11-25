<?php

namespace Database\Factories;

use App\Models\MainWarehouse;
use App\Models\MainWarehouseMedication;
use App\Models\Medication;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A received batch of a medication in the main warehouse.
 *
 * @extends Factory<MainWarehouseMedication>
 */
class MainWarehouseMedicationFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->numberBetween(50, 500);
        $wholesalePrice = fake()->numberBetween(1, 50) * 1_000;

        return [
            'main_warehouse_id' => MainWarehouse::factory(),
            'medication_id' => Medication::factory(),
            'supplier_id' => Supplier::factory(),
            'quantity' => $quantity,
            'batch_number' => fake()->bothify('??-#####'),
            'date_of_receipt' => fake()->dateTimeBetween('-3 months')->format('Y-m-d'),
            'expiration_date' => fake()->dateTimeBetween('+6 months', '+3 years')->format('Y-m-d'),
            'wholesale_price' => $wholesalePrice,
            'markup' => 25,
            'retail_price' => $wholesalePrice * 1.25,
            'incoming_price_per_unit' => $wholesalePrice,
            'incoming_price_total' => $wholesalePrice * $quantity,
            'incoming_sum' => $wholesalePrice * $quantity,
            'total_price' => $wholesalePrice * 1.25 * $quantity,
        ];
    }
}
