<?php

namespace App\Services;

use App\Models\MedicalDevice;

class MedicalDeviceService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(array $validated): MedicalDevice
    {
        return MedicalDevice::query()->create($validated);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(MedicalDevice $device, array $validated): MedicalDevice
    {
        $device->update($validated);

        return $device->refresh();
    }

    public function destroy(MedicalDevice $device): void
    {
        $device->delete();
    }
}
