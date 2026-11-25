<?php

namespace Database\Factories;

use App\Enums\ServicePlace;
use App\Enums\ServiceStatus;
use App\Models\Patient;
use App\Models\PatientService;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A service from the price list assigned to a patient.
 *
 * @extends Factory<PatientService>
 */
class PatientServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'patient_id' => Patient::factory(),
            'service_date' => now()->toDateString(),
            'service_time' => '10:00',
            'place' => ServicePlace::Institution,
            'status' => ServiceStatus::Assigned,
            // Kept at ordering, as PatientServiceAssignService does.
            'price' => fn (array $attributes) => Service::query()->whereKey($attributes['service_id'])->first()?->price,
            'currency' => fn (array $attributes) => Service::query()->whereKey($attributes['service_id'])->first()?->priceCurrency(),
        ];
    }

    /** Confirmed by the registrar and waiting for payment at the cashier. */
    public function confirmed(): static
    {
        return $this->state(fn (): array => ['status' => ServiceStatus::Confirmed]);
    }
}
