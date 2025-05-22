<?php

namespace App\Services;

use App\Models\PatientDiagnosis;

class PatientDiagnosisService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): PatientDiagnosis
    {
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
