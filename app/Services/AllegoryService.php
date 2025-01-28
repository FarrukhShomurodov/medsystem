<?php

namespace App\Services;

use App\Models\Allegory;

class AllegoryService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Allegory
    {
        return Allegory::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Allegory $allegory, array $validated): Allegory
    {
        $allegory->update($validated);
        return $allegory->refresh();
    }

    /**
     * @param Allegory $allegory
     * @return void
     */
    public function destroy(Allegory $allegory): void
    {
        $allegory->delete();
    }
}
