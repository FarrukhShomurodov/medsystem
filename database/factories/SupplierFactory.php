<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake('ru_RU')->company(),
            'contract_terms' => 'Отсрочка платежа 30 дней',
            'phone' => fake()->numerify('+998 71 ###-##-##'),
            'email' => fake()->safeEmail(),
            'address' => 'г. Ташкент, ул. '.fake('ru_RU')->streetName().', '.fake()->numberBetween(1, 90),
            'delivery_schedule' => fake()->randomElement(['Пн, Чт', 'Вт, Пт', 'Ежедневно']),
        ];
    }
}
