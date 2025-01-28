<?php

namespace App\Services;

use App\Models\Institution;

class InstitutionService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Institution
    {
        return Institution::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Institution $institution, array $validated): Institution
    {
        $institution->update($validated);
        return $institution->refresh();
    }

    /**
     * @param Institution $institution
     * @return void
     */
    public function destroy(Institution $institution): void
    {
        $institution->delete();
    }
}
