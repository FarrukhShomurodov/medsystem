<?php

namespace App\Services;

use App\Models\ServiceStaff;
use App\Models\ServiceStaffSpecialty;

class ServiceStaffService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): ServiceStaff
    {
        if (!is_numeric($validated['service_staff_specialty_id'])) {
            $validated['service_staff_specialty_id'] = ServiceStaffSpecialty::query()->create(['name' => $validated['service_staff_specialty_id']])->id;
        }

        return ServiceStaff::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(ServiceStaff $doctor, array $validated): ServiceStaff
    {
        if (!is_numeric($validated['service_staff_specialty_id'])) {
            $validated['service_staff_specialty_id'] = ServiceStaffSpecialty::query()->create(['name' => $validated['service_staff_specialty_id']])->id;
        }

        $doctor->update($validated);
        return $doctor->refresh();
    }

    /**
     * @param ServiceStaff $staff
     * @return void
     */
    public function destroy(ServiceStaff $staff): void
    {
        $staff->delete();
    }
}
