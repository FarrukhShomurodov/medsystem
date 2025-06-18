<?php

namespace App\Services;

use App\Models\Medication;
use App\Models\Patient;
use App\Models\PatientMedication;


class PatientMedicationService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): void
    {
        if (isset($validated['medications'])) {
            $medicationData = [];
            $medicationIndexMap = [];

            foreach ($validated['medications'] as $index => $medicationId) {
                $medicationModel = Medication::find((int)$medicationId);
                if (!$medicationModel) {
                    continue;
                }

                $medicationData[$medicationModel->id] = [
                    'dosage' => $validated['dosages'][$index] ?? null,
                    'admission_period' => $validated['admission_periods'][$index] ?? null,
                ];
                $medicationIndexMap[$medicationModel->id] = $index;
            }

            $patient = Patient::query()->find($validated['patient_id']);
            $patient->medications()->sync($medicationData);

            foreach ($patient->medications as $pivotedMedication) {
                $pivotId = $pivotedMedication->pivot->id;
                $pivotModel = PatientMedication::find($pivotId);
                if (!$pivotModel) {
                    continue;
                }

                $index = $medicationIndexMap[$pivotedMedication->id] ?? null;
                if ($index === null) {
                    continue;
                }

                $compatibles = $validated['compatible_medications'][$index] ?? [];
                $dosagesCompatibles = $validated['compatible_dosages'][$index] ?? [];

                $syncData = [];
                foreach ($compatibles as $i => $compId) {
                    $syncData[$compId] = [
                        'dosage' => $dosagesCompatibles[$i] ?? null,
                    ];
                }

                $pivotModel->compatibleMedications()->sync($syncData);
            }
        }
    }
}
