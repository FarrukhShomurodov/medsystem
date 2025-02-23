<?php

namespace App\Services;

use App\Models\StaffPosition;

class StaffPositionService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): StaffPosition
    {
        return StaffPosition::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(StaffPosition $position, array $validated): StaffPosition
    {
        $position->update($validated);
        return $position->refresh();
    }

    /**
     * @param StaffPosition $position
     * @return void
     */
    public function destroy(StaffPosition $position): void
    {
        $position->delete();
    }
}
