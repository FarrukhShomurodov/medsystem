<?php

namespace Database\Factories;

use App\Models\WardRoom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WardRoom>
 */
class WardRoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'room_number' => fake()->numerify('###'),
        ];
    }
}
