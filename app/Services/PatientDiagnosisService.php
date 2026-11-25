<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientDiagnosis;

class PatientDiagnosisService
{
    /**
     * @param  array{patient_id: int, diagnosis_id: int}  $validated
     */
    public function store(array $validated): PatientDiagnosis
    {
        $patient = Patient::query()->findOrFail($validated['patient_id']);

        return PatientDiagnosis::query()->create([
            'patient_id' => $patient->id,
            'examination_map_id' => $patient->activeExaminationMap()->value('id'),
            'diagnosis_id' => (int) $validated['diagnosis_id'],
        ]);
    }
}
