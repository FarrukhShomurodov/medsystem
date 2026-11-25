<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\ExaminationMap;
use App\Models\Patient;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * An inpatient examination map (hospital stay) of a patient.
 *
 * @extends Factory<ExaminationMap>
 */
class ExaminationMapFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'staff_id' => Staff::factory(),
            'department_id' => Department::factory(),
            'admission_date' => fake()->dateTimeBetween('-10 days')->format('Y-m-d'),
            'admission_diagnosis_code' => 'I10',
            'admission_diagnosis_description' => 'Эссенциальная (первичная) гипертензия',
            'examination_purpose' => 'Обследование и лечение',
            'complaints' => 'Головная боль, слабость, головокружение',
            'is_active' => true,
            'is_outpatient' => false,
        ];
    }

    public function outpatient(): static
    {
        return $this->state(fn (): array => ['is_outpatient' => true]);
    }

    public function discharged(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
            'discharge_date' => now()->toDateString(),
        ]);
    }
}
