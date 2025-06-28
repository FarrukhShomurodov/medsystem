<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Procedure;

class PatientProcedureService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): void
    {
        $patient = Patient::query()->find($validated['patient_id']);
        $map = Patient::find($validated['patient_id'])->examinationMaps()->where('is_active', true);

        if (isset($validated['procedures'])) {
            $procedureData = [];

            foreach ($validated['procedures'] as $index => $procedure) {
                if (!is_numeric($procedure)) {
                    $procedureModel = Procedure::firstOrCreate(['name' => trim($procedure)]);
                } else {
                    $procedureModel = Procedure::findOrFail($procedure);
                }

                $procedureData[$procedureModel->id] = [
                    'examination_map_id' => $map->exists() ? $map->first()->id : null,
                    'procedure_date' => $validated['procedure_dates'][$index]
                ];
            }

            $patient->procedures()->attach($procedureData);
        }
    }
}
