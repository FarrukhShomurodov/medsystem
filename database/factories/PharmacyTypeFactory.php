<?php

namespace Database\Factories;

use App\Models\PharmacyType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PharmacyType>
 */
class PharmacyTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Больничная аптека', 'Аптечный пункт']),
        ];
    }
}
