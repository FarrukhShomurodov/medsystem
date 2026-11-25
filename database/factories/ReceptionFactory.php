<?php

namespace Database\Factories;

use App\Enums\ReceptionStatus;
use App\Models\Patient;
use App\Models\Reception;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * An appointment of a patient with a doctor, today.
 *
 * @extends Factory<Reception>
 */
class ReceptionFactory extends Factory
{
    /** Consecutive appointments get consecutive 20-minute slots, so they never overlap (receptions_no_overlap). */
    private static int $slot = 0;

    public function definition(): array
    {
        $start = today()->setTime(8, 0)->addMinutes(20 * (self::$slot++ % 30));

        return [
            'patient_id' => Patient::factory(),
            'staff_id' => Staff::factory(),
            'service_id' => Service::factory()->appointment(),
            'date' => today()->toDateString(),
            'start_time' => $start->format('H:i'),
            'end_time' => $start->copy()->addMinutes(20)->format('H:i'),
            'status' => ReceptionStatus::New,
            // Kept at booking, as ReceptionService does.
            'price' => fn (array $attributes) => Service::query()->whereKey($attributes['service_id'])->first()?->price,
            'currency' => fn (array $attributes) => Service::query()->whereKey($attributes['service_id'])->first()?->priceCurrency(),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (): array => ['status' => ReceptionStatus::Confirmed]);
    }
}
