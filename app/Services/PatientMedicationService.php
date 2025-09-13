<?php

namespace App\Services;

use App\Models\MainWarehouse;
use App\Models\MainWarehouseMedication;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\PatientMedication;
use Carbon\Carbon;


class PatientMedicationService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): void
    {
        $patient = Patient::query()->find($validated['patient_id']);
        $map = Patient::find($validated['patient_id'])->examinationMaps()->where('is_active', true);

        if (isset($validated['medications'])) {
            $medicationData = [];
            $medicationIndexMap = [];

            foreach ($validated['medications'] as $index => $medicationId) {
                $medicationModel = Medication::find((int)$medicationId);
                if (!$medicationModel) {
                    continue;
                }

                $mainWarehouseMedication = MainWarehouseMedication::query()->where('main_warehouse_id', 1)->where('medication_id', $medicationId)->first();

                $pricePerUnit = $mainWarehouseMedication->retail_price
                    ?? $mainWarehouseMedication->incoming_price_per_unit
                    ?? 0;

                $totalPrice = $pricePerUnit * (int)$validated['dosages'][$index];

                $now = now();

                $medicationData[$medicationModel->id] = [
                    'dosage' => $validated['dosages'][$index] ?? null,
                    'single_dose' => $validated['single_doses'][$index] ?? null,
                    'status' => $validated['statuses'][$index] ?? null,
                    'method' => $validated['methods'][$index] ?? null,
                    'admission_rules' => $validated['admission_rules'][$index] ?? null,
                    'date_of_end' => $validated['date_of_ends'][$index] ?? null,

                    'medication_notes' => $validated['medication_notes'][$index] ?? null,

                    'admission_period' => $validated['admission_periods'][$index] ?? null,
                    'examination_map_id' => $map->exists() ? $map->first()->id : null,

                    'price' => $totalPrice ?? 0,

                    'created_at' => $now,
                ];
                $medicationIndexMap[$medicationModel->id] = $index;
            }

            $patient->medications()->attach($medicationData);

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

    public function cancel(PatientMedication $medication)
    {
        $medication->update([
            'status' => 'cancelled'
        ]);

        return $medication;
    }

    public function update(PatientMedication $medication, array $validated): void
    {
        $now = now();

        $mainWarehouseMedication = MainWarehouseMedication::query()->where('main_warehouse_id', 1)->where('medication_id', $medication->medication->id)->first();

        $pricePerUnit = $mainWarehouseMedication->retail_price
            ?? $mainWarehouseMedication->incoming_price_per_unit
            ?? 0;

        $totalPrice = $pricePerUnit * (int)$validated['dosages'][0];

        // обновляем pivot самого препарата
        $medication->update([
            'dosage'           => $validated['dosages'][0] ?? $medication->dosage,
            'single_dose'      => $validated['single_doses'][0] ?? $medication->single_dose,
            'status'           => $validated['statuses'][0] ?? $medication->status,
            'method'           => $validated['methods'][0] ?? $medication->method,
            'admission_rules'  => $validated['admission_rules'][0] ?? $medication->admission_rules,
            'date_of_end'      => $validated['date_of_ends'][0] ?? $medication->date_of_end,
            'medication_notes' => $validated['medication_notes'][0] ?? $medication->medication_notes,
            'admission_period' => $validated['admission_periods'][0] ?? $medication->admission_period,
            'price' => $totalPrice ?? 0,
            'updated_at'       => $now,
        ]);

        // Обновляем совместимые препараты
        $compatibles = $validated['compatible_medications'] ?? [];
        $dosagesCompatibles = $validated['compatible_dosages'] ?? [];

        $syncData = [];
        foreach ($compatibles as $i => $compId) {
            $syncData[$compId] = [
                'dosage' => $dosagesCompatibles[$i] ?? null,
            ];
        }

        $medication->compatibleMedications()->sync($syncData);
    }

    public function extend(PatientMedication $medication): ?PatientMedication
    {
        if ($medication->status !== 'cancelled' || $medication->status !== 'returned' || $medication->status !== 'cancelled') {
            $newDate = Carbon::parse($medication->date_of_end)->addDay();

            $medication->update([
                'date_of_end' => $newDate,
            ]);

            return $medication;
        }

        return null;
    }
}
