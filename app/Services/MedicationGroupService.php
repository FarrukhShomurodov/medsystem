<?php

namespace App\Services;

use App\Models\MedicationGroup;

class MedicationGroupService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(array $validated): MedicationGroup
    {
        return MedicationGroup::query()->create($validated);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(MedicationGroup $group, array $validated): MedicationGroup
    {
        $group->update($validated);

        return $group->refresh();
    }

    public function destroy(MedicationGroup $group): void
    {
        $group->delete();
    }
}
