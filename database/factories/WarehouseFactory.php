<?php

namespace Database\Factories;

use App\Models\Warehouse;
use App\Models\WarehouseType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A department-level warehouse that requests stock from the main warehouse.
 *
 * @extends Factory<Warehouse>
 */
class WarehouseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Склад '.fake()->randomElement(['терапии', 'хирургии', 'кардиологии', 'педиатрии']),
            'description' => 'Склад отделения',
            'warehouse_type_id' => WarehouseType::factory(),
            'address' => 'Корпус '.fake()->numberBetween(1, 4).', цокольный этаж',
        ];
    }
}
