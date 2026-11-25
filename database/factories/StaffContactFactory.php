<?php

namespace Database\Factories;

use App\Models\Staff;
use App\Models\StaffContact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffContact>
 */
class StaffContactFactory extends Factory
{
    public function definition(): array
    {
        return [
            'staff_id' => Staff::factory(),
            'phone' => fake()->numerify('+998 9# ###-##-##'),
            'email' => fake()->safeEmail(),
            'address' => 'г. Ташкент, ул. '.fake('ru_RU')->streetName().', '.fake()->numberBetween(1, 90),
        ];
    }
}
