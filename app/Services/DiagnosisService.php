<?php

namespace App\Services;

use App\Models\Diagnosis;

class DiagnosisService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Diagnosis
    {
        return Diagnosis::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Diagnosis $diagnosis, array $validated): Diagnosis
    {
        $diagnosis->update($validated);
        return $diagnosis->refresh();
    }

    /**
     * @param Diagnosis $diagnosis
     * @return void
     */
    public function destroy(Diagnosis $diagnosis): void
    {
        $diagnosis->delete();
    }
}
