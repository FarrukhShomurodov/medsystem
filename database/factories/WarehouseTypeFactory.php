<?php

namespace Database\Factories;

use App\Models\WarehouseType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WarehouseType>
 */
class WarehouseTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Медикаменты', 'Хозяйственный материал', 'ИМН']),
        ];
    }
}
