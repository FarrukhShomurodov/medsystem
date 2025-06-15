<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientDiagnosis;

class PatientDiagnosisService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): PatientDiagnosis
    {
        $patient = Patient::find($validated['patient_id']);

        if ($patient->examinationMaps->where('is_active', true)->isEmpty()) {
            unset($validated['examination_map_id']);
        } else {
            $validated['examination_map_id'] = $patient->examinationMaps()->where('is_active', true)->first()->id;
        }

        return PatientDiagnosis::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(PatientDiagnosis $diagnosis, array $validated): PatientDiagnosis
    {
        $diagnosis->update($validated);
        return $diagnosis->refresh();
    }

    /**
     * @param PatientDiagnosis $diagnosis
     * @return void
     */
    public function destroy(PatientDiagnosis $diagnosis): void
    {
        $diagnosis->delete();
    }
}
