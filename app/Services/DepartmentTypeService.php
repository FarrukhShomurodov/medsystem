<?php

namespace App\Services;

use App\Models\DepartmentType;

class DepartmentTypeService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): DepartmentType
    {
        return DepartmentType::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(DepartmentType $type, array $validated): DepartmentType
    {
        $type->update($validated);
        return $type->refresh();
    }

    /**
     * @param DepartmentType $type
     * @return void
     */
    public function destroy(DepartmentType $type): void
    {
        $type->delete();
    }
}
