<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DepartmentService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(array $validated): Department
    {
        return DB::transaction(function () use ($validated): Department {
            $department = Department::query()->create(Arr::except($validated, 'rooms'));
            $department->rooms()->sync($validated['rooms']);

            return $department;
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(Department $department, array $validated): Department
    {
        return DB::transaction(function () use ($department, $validated): Department {
            $department->update(Arr::except($validated, 'rooms'));
            $department->rooms()->sync($validated['rooms']);

            return $department;
        });
    }

    public function destroy(Department $department): void
    {
        $department->delete();
    }
}
