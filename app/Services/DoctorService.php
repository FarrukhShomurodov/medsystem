<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Specialty;

class DoctorService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Doctor
    {
        if (!is_numeric($validated['specialty_id'])) {
            $validated['specialty_id'] = Specialty::query()->create(['name' => $validated['specialty_id']])->id;
        }

        return Doctor::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Doctor $doctor, array $validated): Doctor
    {
        if (!is_numeric($validated['specialty_id'])) {
            $validated['specialty_id'] = Specialty::query()->create(['name' => $validated['specialty_id']])->id;
        }

        $doctor->update($validated);
        return $doctor->refresh();
    }

    /**
     * @param Doctor $doctor
     * @return void
     */
    public function destroy(Doctor $doctor): void
    {
        $doctor->delete();
    }
}
