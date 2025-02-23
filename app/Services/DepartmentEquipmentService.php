<?php

namespace App\Services;

use App\Models\DepartmentEquipment;

class DepartmentEquipmentService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): DepartmentEquipment
    {
        return DepartmentEquipment::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(DepartmentEquipment $equipment, array $validated): DepartmentEquipment
    {
        $equipment->update($validated);
        return $equipment->refresh();
    }

    /**
     * @param DepartmentEquipment $equipment
     * @return void
     */
    public function destroy(DepartmentEquipment $equipment): void
    {
        $equipment->delete();
    }
}
