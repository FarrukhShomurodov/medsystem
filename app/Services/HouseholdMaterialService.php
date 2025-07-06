<?php

namespace App\Services;

use App\Models\HouseholdMaterial;

class HouseholdMaterialService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): HouseholdMaterial
    {
        return HouseholdMaterial::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(HouseholdMaterial $material, array $validated): HouseholdMaterial
    {
        $material->update($validated);
        return $material->refresh();
    }

    /**
     * @param HouseholdMaterial $material
     * @return void
     */
    public function destroy(HouseholdMaterial $material): void
    {
        $material->delete();
    }
}
