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
            $validated['department_id'] = $department->id;

            // Создание связанных записей
            $department->contact()->create($validated);
            $department->bed()->create($validated);
            $department->employee()->create($validated);
            $department->license()->create($validated);

            // Синхронизация услуг
            if (!empty($validated['services'])) {
                $department->services()->sync($validated['services']);
            }

            // Синхронизация отношений
            if (!empty($validated['relations'])) {
                $department->relations()->sync($validated['relations']);
            }

            // Синхронизация оборудование
            if (!empty($validated['equipments'])) {
                $department->equipments()->sync($validated['equipments']);
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
            $validated['department_id'] = $department->id;

            // Создание связанных записей
            $department->contact()->create($validated);
            $department->bed()->create($validated);
            $department->employee()->create($validated);
            $department->license()->create($validated);

            // Синхронизация услуг
            if (!empty($validated['services'])) {
                $department->services()->sync($validated['services']);
            }

            // Синхронизация отношений
            if (!empty($validated['relations'])) {
                $department->relations()->sync($validated['relations']);
            }

            // Синхронизация оборудование
            if (!empty($validated['equipments'])) {
                $department->equipments()->sync($validated['equipments']);
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
