<?php

namespace App\Services;

use App\Models\Specialty;

class SpecialtyService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Specialty
    {
        return Specialty::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Specialty $specialty, array $validated): Specialty
    {
        $specialty->update($validated);
        return $specialty->refresh();
    }

    /**
     * @param Specialty $specialty
     * @return void
     */
    public function destroy(Specialty $specialty): void
    {
        $specialty->delete();
    }
}
