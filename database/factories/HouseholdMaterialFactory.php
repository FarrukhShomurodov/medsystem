<?php

namespace Database\Factories;

use App\Models\HouseholdMaterial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HouseholdMaterial>
 */
class HouseholdMaterialFactory extends Factory
{
    public function definition(): array
    {
        $unitPrice = fake()->numberBetween(5, 50) * 1_000;

        return [
            'batch_number' => fake()->bothify('ХМ-#####'),
            'name' => fake()->randomElement(['Мыло жидкое', 'Бумажные полотенца', 'Дезинфицирующее средство', 'Мешки для мусора']),
            'entry_price_unit' => $unitPrice,
            'entry_price_total' => $unitPrice * 10,
        ];
    }
}
