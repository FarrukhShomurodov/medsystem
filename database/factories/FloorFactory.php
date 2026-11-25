<?php

namespace Database\Factories;

use App\Models\Floor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Floor>
 */
class FloorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->numberBetween(1, 9).' этаж',
        ];
    }
}
