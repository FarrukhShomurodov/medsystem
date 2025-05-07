<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Reception;

class ReceptionService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated)
    {
        $patient = Patient::find($validated['patient_id']);

        if ($patient->examinationMaps->where('is_active', true)->isEmpty()) {
            unset($validated['examination_map_id']);
        } else {
            $validated['examination_map_id'] = $patient->examinationMaps()->where('is_active', true)->first()->id;
        }

        return Reception::query()->create($validated);
    }

    /**
     * @param Reception $reception
     * @param array $validated
     * @return Reception|null
     */
    public function update(Reception $reception, array $validated): ?Reception
    {
        $reception->update($validated);
        return $reception->fresh();
    }

    /**
     * @param Reception $reception
     * @return void
     */
    public function destroy(Reception $reception): void
    {
        $reception->delete();
    }
}
