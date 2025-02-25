<?php

namespace App\Services;

use App\Models\WarehouseType;

class WarehouseTypeService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): WarehouseType
    {
        return WarehouseType::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(WarehouseType $type, array $validated): WarehouseType
    {
        $type->update($validated);
        return $type->refresh();
    }

    /**
     * @param WarehouseType $type
     * @return void
     */
    public function destroy(WarehouseType $type): void
    {
        $type->delete();
    }
}
