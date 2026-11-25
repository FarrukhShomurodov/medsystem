<?php

namespace App\Services;

use App\Models\Office;

class OfficeService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(array $validated): Office
    {
        return Office::query()->create($validated);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(Office $office, array $validated): Office
    {
        $office->update($validated);

        return $office->refresh();
    }

    public function destroy(Office $office): void
    {
        $office->delete();
    }
}
