<?php

namespace App\Services;

use App\Models\Medication;
use Illuminate\Support\Facades\DB;

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
            $medication->riskAndSafety()->create($validated);
            $medication->composition()->create($validated);
//            $medication->packaging()->create($validated);
            $medication->storage()->create($validated);
            $medication->licenseAndCertification()->create($validated);
//            $medication->medicationCost()->create($validated);

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
            $medication->riskAndSafety()->update(
                array_intersect_key($validated, array_flip($medication->riskAndSafety->getFillable()))
            );
            $medication->composition()->update(
                array_intersect_key($validated, array_flip($medication->composition->getFillable()))
            );
            $medication->packaging()->update(
                array_intersect_key($validated, array_flip($medication->packaging->getFillable()))
            );
            $medication->storage()->update(
                array_intersect_key($validated, array_flip($medication->storage->getFillable()))
            );
            $medication->licenseAndCertification()->update(
                array_intersect_key($validated, array_flip($medication->licenseAndCertification->getFillable()))
            );
            $medication->medicationCost()->update(
                array_intersect_key($validated, array_flip($medication->medicationCost->getFillable()))
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
