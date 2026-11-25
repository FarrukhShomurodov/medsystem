<?php

namespace Database\Factories;

use App\Models\Diagnosis;
use App\Models\Patient;
use App\Models\PatientDiagnosis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PatientDiagnosis>
 */
class PatientDiagnosisFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'diagnosis_id' => Diagnosis::factory(),
        ];
    }
}
