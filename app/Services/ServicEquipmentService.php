<?php

namespace App\Services;

use App\Models\ServiceEquipment;

class ServicEquipmentService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): ServiceEquipment
    {
        return ServiceEquipment::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(ServiceEquipment $equipment, array $validated): ServiceEquipment
    {
        $equipment->update($validated);
        return $equipment->refresh();
    }

    /**
     * @param ServiceEquipment $equipment
     * @return void
     */
    public function destroy(ServiceEquipment $equipment): void
    {
        $equipment->delete();
    }
}
