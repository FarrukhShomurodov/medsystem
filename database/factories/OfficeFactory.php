<?php

namespace Database\Factories;

use App\Models\Floor;
use App\Models\Office;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Office>
 */
class OfficeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Кабинет '.fake()->numerify('###'),
            'floor_id' => Floor::factory(),
        ];
    }
}
