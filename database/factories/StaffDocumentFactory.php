<?php

namespace Database\Factories;

use App\Models\Staff;
use App\Models\StaffDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffDocument>
 */
class StaffDocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'staff_id' => Staff::factory(),
            'employment_contract_number' => fake()->numerify('ТД-####/##'),
            'employment_contract_date_of_signing' => fake()->dateTimeBetween('-10 years', '-1 month')->format('Y-m-d'),
            'employment_contract_validity_period' => 'Бессрочный',
            'passport_series' => fake()->regexify('[A-Z]{2}[0-9]{7}'),
            'INN' => fake()->numerify('#########'),
            'SNILS' => fake()->numerify('##############'),
            'is_work' => true,
        ];
    }

    public function dismissed(): static
    {
        return $this->state(fn (): array => [
            'is_work' => false,
            'dismissal_orders_number' => fake()->numerify('П-###'),
            'dismissal_orders_date' => fake()->dateTimeBetween('-1 year')->format('Y-m-d'),
        ]);
    }
}
