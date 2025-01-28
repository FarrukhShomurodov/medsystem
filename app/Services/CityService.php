<?php

namespace App\Services;

use App\Models\City;
use App\Models\Region;

class CityService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): City
    {
        if (!is_numeric($validated['region_id'])) {
            $validated['region_id'] = Region::query()->create(['name' => $validated['region_id']])->id;
        }

        return City::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(City $city, array $validated): City
    {
        if (!is_numeric($validated['region_id'])) {
            $validated['region_id'] = Region::query()->create(['name' => $validated['region_id']])->id;
        }

        $city->update($validated);
        return $city->refresh();
    }

    /**
     * @param City $city
     * @return void
     */
    public function destroy(City $city): void
    {
        $city->delete();
    }
}
