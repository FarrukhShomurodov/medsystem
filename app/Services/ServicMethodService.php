<?php

namespace App\Services;

use App\Models\ServiceMethod;

class ServicMethodService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): ServiceMethod
    {
        return ServiceMethod::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(ServiceMethod $method, array $validated): ServiceMethod
    {
        $method->update($validated);
        return $method->refresh();
    }

    /**
     * @param ServiceMethod $method
     * @return void
     */
    public function destroy(ServiceMethod $method): void
    {
        $method->delete();
    }
}
