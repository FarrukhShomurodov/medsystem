<?php

namespace App\Services;

use App\Models\MedicalEquipment;

class MedicalEquipmentService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): MedicalEquipment
    {
        return MedicalEquipment::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(MedicalEquipment $equipment, array $validated): MedicalEquipment
    {
        $equipment->update($validated);
        return $equipment->refresh();
    }

    /**
     * @param MedicalEquipment $equipment
     * @return void
     */
    public function destroy(MedicalEquipment $equipment): void
    {
        $equipment->delete();
    }
}
