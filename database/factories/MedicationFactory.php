<?php

namespace Database\Factories;

use App\Models\Manufacturer;
use App\Models\Medication;
use App\Models\MedicationGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Medication>
 */
class MedicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Парацетамол', 'Ибупрофен', 'Амоксициллин', 'Омепразол']),
            'release_form' => fake()->randomElement(['Таблетка', 'Капсула', 'Ампула', 'Сироп']),
            'dosage' => fake()->randomElement(['250 мг', '500 мг', '1 г']),
            'manufacturer_id' => Manufacturer::factory(),
            'group_id' => MedicationGroup::factory(),
            'registration_number' => fake()->numerify('DV/X ##### ##/##'),
            'registration_date' => fake()->dateTimeBetween('-8 years', '-1 month')->format('Y-m-d'),
            'min_deduction' => 1,
            'min_quantity' => 20,
            'notify_before_days' => 30,
            'notify_min_qty' => 20,
        ];
    }
}
