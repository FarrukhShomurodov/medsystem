<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Reception;

class ReceptionService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Reception
    {
        $validated['examination_map_id'] = Patient::find($validated['patient_id'])->examinationMaps()->where('is_active', 1)->first()->id;

        return Reception::query()->create($validated);
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
