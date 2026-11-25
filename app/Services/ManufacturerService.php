<?php

namespace App\Services;

use App\Models\Manufacturer;

class ManufacturerService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(array $validated): Manufacturer
    {
        return Manufacturer::query()->create($validated);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(Manufacturer $manufacturer, array $validated): Manufacturer
    {
        $manufacturer->update($validated);

        return $manufacturer->refresh();
    }

    public function destroy(Manufacturer $manufacturer): void
    {
        $manufacturer->delete();
    }
}
