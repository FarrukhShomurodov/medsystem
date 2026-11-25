<?php

namespace Database\Factories;

use App\Enums\AdministrationRoute;
use App\Enums\AdmissionRule;
use App\Enums\PatientMedicationStatus;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\PatientMedication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A prescription: a medication ordered for a patient.
 *
 * @extends Factory<PatientMedication>
 */
class PatientMedicationFactory extends Factory
{
    public function definition(): array
    {
        $dosage = fake()->numberBetween(1, 3);

        return [
            'patient_id' => Patient::factory(),
            'medication_id' => Medication::factory(),
            'dosage' => (string) $dosage,
            'single_dose' => '1 таблетка',
            'admission_period' => fake()->randomElement(['2 раза в день', '3 раза в день', '1 раз в день']),
            'status' => PatientMedicationStatus::Ordered,
            'method' => AdministrationRoute::Oral,
            'admission_rules' => AdmissionRule::AfterMeal,
            'date_of_end' => now()->addDays(fake()->numberBetween(3, 10))->toDateString(),
            'price' => $dosage * 2_500,
        ];
    }

    /** Sent from the pharmacy to the ward and waiting to be accepted (and paid) at the nurse station. */
    public function sentToFloor(): static
    {
        return $this->state(fn (): array => ['status' => PatientMedicationStatus::SentToFloor]);
    }
}
