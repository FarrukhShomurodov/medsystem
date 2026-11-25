<?php

namespace App\Services;

use App\Models\ServiceMethod;

class ServiceMethodService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(array $validated): ServiceMethod
    {
        return ServiceMethod::query()->create($validated);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(ServiceMethod $method, array $validated): ServiceMethod
    {
        $method->update($validated);

        return $method->refresh();
    }

    public function destroy(ServiceMethod $method): void
    {
        $method->delete();
    }
}
