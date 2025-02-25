<?php

namespace App\Services;

use App\Models\MedicalSupply;

class MedicalSupplyService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): MedicalSupply
    {
        return MedicalSupply::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(MedicalSupply $supply, array $validated): MedicalSupply
    {
        $supply->update($validated);
        return $supply->refresh();
    }

    /**
     * @param MedicalSupply $supply
     * @return void
     */
    public function destroy(MedicalSupply $supply): void
    {
        $supply->delete();
    }
}
