<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use Illuminate\Support\Facades\DB;

class ServiceService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Service
    {
        $service = DB::transaction(function () use ($validated) {
            if (!is_numeric($validated['service_type_id'])) {
                $validated['service_type_id'] = ServiceType::query()->create(['name' => $validated['service_type_id']])->id;
            }

            return Service::query()->create($validated);
        });

        return $service;
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Service $service, array $validated)
    {
        return DB::transaction(function () use ($validated, $service) {
            if (!is_numeric($validated['service_type_id'])) {
                $validated['service_type_id'] = ServiceType::query()->create(['name' => $validated['service_type_id']])->id;
            }

            return $service->update($validated);
        });
    }

    /**
     * @param Service $service
     * @return void
     */
    public function destroy(Service $service): void
    {
        $service->delete();
    }
}
