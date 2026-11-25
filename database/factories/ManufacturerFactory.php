<?php

namespace Database\Factories;

use App\Models\Manufacturer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Manufacturer>
 */
class ManufacturerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake('ru_RU')->company(),
            'country_of_manufacture' => fake()->randomElement(['Узбекистан', 'Россия', 'Индия', 'Германия', 'Венгрия']),
        ];
    }
}
