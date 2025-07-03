<?php

namespace App\Services;

use App\Models\EquipmentType;

class EquipmentTypeService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): EquipmentType
    {
        return EquipmentType::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(EquipmentType $type, array $validated): EquipmentType
    {
        $type->update($validated);

        return $type->refresh();
    }

    public function destroy(EquipmentType $type): void
    {
        $type->delete();
    }
}
