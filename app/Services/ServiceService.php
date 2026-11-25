<?php

namespace App\Services;

use App\Models\Service;

/**
 * The price list stores what the cash desk charges, VAT included, exactly as the form or the
 * import sheet gives it: orders copy services.price (ADR 0007) and billing never adjusts it.
 */
class ServiceService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(array $validated): Service
    {
        return Service::query()->create($validated);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(Service $service, array $validated): Service
    {
        $service->update($validated);

        return $service;
    }

    public function destroy(Service $service): void
    {
        $service->delete();
    }
}
