<?php

namespace App\Services;

use App\Models\Region;

class RegionService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Region
    {
        return Region::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Region $region, array $validated): Region
    {
        $region->update($validated);
        return $region->refresh();
    }

    /**
     * @param Region $region
     * @return void
     */
    public function destroy(Region $region): void
    {
        $region->delete();
    }
}
