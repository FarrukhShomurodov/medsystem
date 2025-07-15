<?php

namespace App\Services;

use App\Models\Medication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MedicationService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Medication
    {
        return DB::transaction(function () use ($validated) {
            $medication = Medication::query()->create($validated);

            $validated['medication_id'] = $medication->id;

            $medication->application()->create($validated);
            $medication->additionalData()->create($validated);
            $medication->composition()->create($validated);
            $medication->storage()->create($validated);

            foreach (['sales_license', 'quality_certificate', 'clinical_trials', 'registration_documents'] as $fileField) {
                if (!empty($validated[$fileField]) && $validated[$fileField] instanceof \Illuminate\Http\UploadedFile) {
                    $validated[$fileField] = $validated[$fileField]->store('medical_licenses', 'public');
                }
            }

            $medication->licenseAndCertification()->create($validated);

            return $medication;
        });
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Medication $medication, array $validated): Medication
    {
        return DB::transaction(function () use ($validated, $medication) {
            $medication->update($validated);

            $validated['medication_id'] = $medication->id;

            $medication->application()->update(
                array_intersect_key($validated, array_flip($medication->application->getFillable()))
            );
            $medication->additionalData()->update(
                array_intersect_key($validated, array_flip($medication->additionalData->getFillable()))
            );
            $medication->composition()->update(
                array_intersect_key($validated, array_flip($medication->composition->getFillable()))
            );
            $medication->storage()->update(
                array_intersect_key($validated, array_flip($medication->storage->getFillable()))
            );

            foreach (['sales_license', 'quality_certificate', 'clinical_trials', 'registration_documents'] as $fileField) {
                if (!empty($validated[$fileField]) && $validated[$fileField] instanceof \Illuminate\Http\UploadedFile) {
                    if ($medication->licenseAndCertification->$fileField) {
                        Storage::disk('public')->delete($medication->licenseAndCertification->$fileField);
                    }

                    $validated[$fileField] = $validated[$fileField]->store('medical_licenses', 'public');
                }
            }

            $medication->licenseAndCertification()->update(
                array_intersect_key($validated, array_flip($medication->licenseAndCertification->getFillable()))
            );

            return $medication;
        });
    }

    /**
     * @param Medication $medication
     * @return void
     */
    public function destroy(Medication $medication): void
    {
        $medication->delete();
    }
}
