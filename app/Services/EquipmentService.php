<?php

namespace App\Services;

use App\Models\Equipment;

class EquipmentService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Equipment
    {
        return Equipment::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Equipment $equipment, array $validated): Equipment
    {
        $equipment->update($validated);
        return $equipment->refresh();
    }

    /**
     * @param Equipment $equipment
     * @return void
     */
    public function destroy(Equipment $equipment): void
    {
        $equipment->delete();
    }
}
