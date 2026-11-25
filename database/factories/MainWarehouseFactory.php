<?php

namespace Database\Factories;

use App\Models\MainWarehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Code still looks the main warehouse up as id 1; real databases get it from WarehouseSeeder.
 *
 * @extends Factory<MainWarehouse>
 */
class MainWarehouseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Общий склад',
        ];
    }
}
