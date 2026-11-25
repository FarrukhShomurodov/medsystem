<?php

namespace App\Services;

use App\Models\ServiceType;

class ServiceTypeService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(array $validated): ServiceType
    {
        return ServiceType::query()->create($validated);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(ServiceType $type, array $validated): ServiceType
    {
        $type->update($validated);

        return $type->refresh();
    }

    public function destroy(ServiceType $type): void
    {
        $type->delete();
    }
}
