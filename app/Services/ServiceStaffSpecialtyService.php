<?php

namespace App\Services;

use App\Models\ServiceStaffSpecialty;

class ServiceStaffSpecialtyService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): ServiceStaffSpecialty
    {
        return ServiceStaffSpecialty::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(ServiceStaffSpecialty $specialty, array $validated): ServiceStaffSpecialty
    {
        $specialty->update($validated);
        return $specialty->refresh();
    }

    /**
     * @param ServiceStaffSpecialty $specialty
     * @return void
     */
    public function destroy(ServiceStaffSpecialty $specialty): void
    {
        $specialty->delete();
    }
}
