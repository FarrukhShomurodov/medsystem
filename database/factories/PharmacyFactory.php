<?php

namespace Database\Factories;

use App\Models\Pharmacy;
use App\Models\PharmacyType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pharmacy>
 */
class PharmacyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Аптека №'.fake()->numberBetween(1, 20),
            'type_id' => PharmacyType::factory(),
            'legal_address' => 'г. Ташкент, ул. '.fake('ru_RU')->streetName().', '.fake()->numberBetween(1, 90),
            'contact_phone' => fake()->numerify('+998 71 ###-##-##'),
            'working_hours' => '08:00–20:00',
        ];
    }
}
