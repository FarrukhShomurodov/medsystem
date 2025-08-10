<?php

namespace App\Services;

use App\Models\Diagnosis;
use App\Models\Survey;

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
        Survey::where('diagnosis_id', $diagnosis->id)->update(['diagnosis_id' => null]);

        if (!empty($validated['surveys'])) {
            Survey::whereIn('id', $validated['surveys'])
                ->update(['diagnosis_id' => $diagnosis->id]);
        }

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
