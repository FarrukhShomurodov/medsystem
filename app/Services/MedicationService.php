<?php

namespace App\Services;

use App\Models\Medication;

class MedicationService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Medication
    {
        return Medication::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Medication $medication, array $validated): Medication
    {
        $medication->update($validated);
        return $medication->refresh();
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
