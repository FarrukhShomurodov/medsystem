<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Support\Facades\DB;

class DepartmentService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Department
    {
        return DB::transaction(function () use ($validated) {
            $department = Department::query()->create($validated);

            // Синхронизация палаты
            if (!empty($validated['rooms'])) {
                $department->rooms()->sync($validated['rooms']);
            }

            return $department;
        }, 5);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Department $department, array $validated)
    {
        return DB::transaction(function () use ($validated, $department) {
            $department->update($validated);

            // Синхронизация палаты
            if (!empty($validated['rooms'])) {
                $department->rooms()->sync($validated['rooms']);
            }
        });
    }

    /**
     * @param Department $department
     * @return void
     */
    public function destroy(Department $department): void
    {
        $department->delete();
    }
}
