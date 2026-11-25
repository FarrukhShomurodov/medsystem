<?php

namespace Database\Factories;

use App\Models\MedicalSupply;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalSupply>
 */
class MedicalSupplyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Шприц 5 мл', 'Система для инфузий', 'Перчатки нитриловые', 'Бинт стерильный']),
        ];
    }
}
