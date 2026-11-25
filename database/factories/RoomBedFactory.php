<?php

namespace Database\Factories;

use App\Models\RoomBed;
use App\Models\WardRoom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomBed>
 */
class RoomBedFactory extends Factory
{
    public function definition(): array
    {
        return [
            'bed_number' => (string) fake()->numberBetween(1, 6),
            'ward_room_id' => WardRoom::factory(),
        ];
    }
}
