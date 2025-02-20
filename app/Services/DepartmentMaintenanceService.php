<?php

namespace App\Services;

use App\Models\DepartmentService;

class DepartmentMaintenanceService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): DepartmentService
    {
        return DepartmentService::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(DepartmentService $service, array $validated): DepartmentService
    {
        $service->update($validated);
        return $service->refresh();
    }

    /**
     * @param DepartmentService $service
     * @return void
     */
    public function destroy(DepartmentService $service): void
    {
        $service->delete();
    }
}
