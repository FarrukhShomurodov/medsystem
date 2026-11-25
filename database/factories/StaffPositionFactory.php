<?php

namespace Database\Factories;

use App\Models\StaffPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffPosition>
 */
class StaffPositionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Доктор', 'Медсестра', 'Старшая медсестра', 'Заведующий', 'Регистратор', 'Кассир']),
        ];
    }
}
