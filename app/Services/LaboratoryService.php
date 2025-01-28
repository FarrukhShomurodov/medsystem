<?php

namespace App\Services;

use App\Models\Laboratory;

class LaboratoryService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Laboratory
    {
        return Laboratory::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Laboratory $laboratory, array $validated): Laboratory
    {
        $laboratory->update($validated);
        return $laboratory->refresh();
    }

    /**
     * @param Laboratory $laboratory
     * @return void
     */
    public function destroy(Laboratory $laboratory): void
    {
        $laboratory->delete();
    }
}
