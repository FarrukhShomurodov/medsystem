<?php

namespace Database\Factories;

use App\Models\ServiceGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceGroup>
 */
class ServiceGroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Консультации', 'Функциональная диагностика', 'Лаборатория', 'Ультразвуковая диагностика']),
        ];
    }
}
