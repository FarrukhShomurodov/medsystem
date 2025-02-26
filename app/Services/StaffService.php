<?php

namespace App\Services;

use App\Models\Staff;
use Illuminate\Support\Facades\DB;

class StaffService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Staff
    {
        return DB::transaction(function () use ($validated) {
            $staff = Staff::query()->create($validated);
            $validated['staff_id'] = $staff->id;

            // Создание связанных записей
            $staff->contact()->create($validated);
            $staff->document()->create($validated);
            $staff->professionalData()->create($validated);

            // Синхронизация услуг
            if (!empty($validated['services'])) {
                $staff->services()->sync($validated['services']);
            }

            return $staff;
        }, 5);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Staff $staff, array $validated)
    {
        return DB::transaction(function () use ($validated, $staff) {

            $staff->update($validated);
            $validated['staff_id'] = $staff->id;

            // Создание связанных записей
            $staff->contact()->update(
                array_intersect_key($validated, array_flip($staff->contact->getFillable()))
            );
            $staff->document()->update(
                array_intersect_key($validated, array_flip($staff->document->getFillable()))
            );
            $staff->professionalData()->update(
                array_intersect_key($validated, array_flip($staff->professionalData->getFillable()))
            );

            // Синхронизация услуг
            if (!empty($validated['services'])) {
                $staff->services()->sync($validated['services']);
            }

            return $staff;
        });
    }

    /**
     * @param Staff $staff
     * @return void
     */
    public function destroy(Staff $staff): void
    {
        $staff->delete();
    }
}
