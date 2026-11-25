<?php

namespace Database\Factories;

use App\Enums\ServiceStatus;
use App\Models\Patient;
use App\Models\PatientProcedure;
use App\Models\Procedure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PatientProcedure>
 */
class PatientProcedureFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'procedure_id' => Procedure::factory(),
            'status' => ServiceStatus::Assigned,
            'procedure_date' => now()->toDateString(),
            // Kept at ordering, as PatientProcedureService does.
            'price' => fn (array $attributes) => Procedure::query()->whereKey($attributes['procedure_id'])->first()?->price,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (): array => ['status' => ServiceStatus::Confirmed]);
    }
}
