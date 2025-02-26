<?php

namespace App\Services;

use App\Models\PharmacyType;

class PharmacyTypeService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): PharmacyType
    {
        return PharmacyType::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(PharmacyType $type, array $validated): PharmacyType
    {
        $type->update($validated);
        return $type->refresh();
    }

    /**
     * @param PharmacyType $type
     * @return void
     */
    public function destroy(PharmacyType $type): void
    {
        $type->delete();
    }
}
