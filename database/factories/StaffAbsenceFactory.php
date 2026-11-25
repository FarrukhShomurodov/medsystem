<?php

namespace Database\Factories;

use App\Enums\AbsenceReason;
use App\Enums\AbsenceType;
use App\Models\Staff;
use App\Models\StaffAbsence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffAbsence>
 */
class StaffAbsenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'staff_id' => Staff::factory(),
            'type' => AbsenceType::Day,
            'start_date' => fake()->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
            'reason' => AbsenceReason::DayOff,
        ];
    }

    public function vacation(): static
    {
        return $this->state(function (): array {
            $start = now()->addDays(fake()->numberBetween(7, 90));

            return [
                'type' => AbsenceType::Period,
                'start_date' => $start->toDateString(),
                'end_date' => $start->copy()->addDays(14)->toDateString(),
                'reason' => AbsenceReason::Vacation,
            ];
        });
    }
}
