<?php

namespace App\Services;

use App\Models\PatientInspection;

class PatientInspectionService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): PatientInspection
    {
        return PatientInspection::query()->create($validated);
    }
}
