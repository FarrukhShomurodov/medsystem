<?php

namespace App\Services;

use App\Models\Floor;

class FloorService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Floor
    {
        return Floor::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Floor $floor, array $validated): Floor
    {
        $floor->update($validated);
        return $floor->refresh();
    }

    /**
     * @param Floor $floor
     * @return void
     */
    public function destroy(Floor $floor): void
    {
        $floor->delete();
    }
}
