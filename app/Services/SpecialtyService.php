<?php

namespace App\Services;

use App\Models\Specialty;

class SpecialtyService
{
    /**
     * @param  array{name: string}  $validated
     */
    public function store(array $validated): Specialty
    {
        return Specialty::query()->create($validated);
    }

    /**
     * @param  array{name: string}  $validated
     */
    public function update(Specialty $specialty, array $validated): Specialty
    {
        $specialty->update($validated);

        return $specialty;
    }

    /**
     * A specialty held by staff is protected by ON DELETE RESTRICT.
     */
    public function destroy(Specialty $specialty): void
    {
        $specialty->delete();
    }
}
