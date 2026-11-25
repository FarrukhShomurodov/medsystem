<?php

namespace App\Services;

use App\Models\City;

class CityService
{
    /**
     * @param  array{name: string, region_id: int}  $validated
     */
    public function store(array $validated): City
    {
        return City::query()->create($validated);
    }

    /**
     * @param  array{name: string, region_id: int}  $validated
     */
    public function update(City $city, array $validated): City
    {
        $city->update($validated);

        return $city;
    }

    public function destroy(City $city): void
    {
        $city->delete();
    }
}
