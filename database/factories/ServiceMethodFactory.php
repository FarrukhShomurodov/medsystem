<?php

namespace Database\Factories;

use App\Models\ServiceMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceMethod>
 */
class ServiceMethodFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Амбулаторно', 'Стационарно', 'На дому', 'Дистанционно']),
        ];
    }
}
