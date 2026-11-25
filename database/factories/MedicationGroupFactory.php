<?php

namespace Database\Factories;

use App\Models\MedicationGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicationGroup>
 */
class MedicationGroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Анальгетики', 'Антибиотики', 'НПВС', 'Антигипертензивные средства']),
        ];
    }
}
