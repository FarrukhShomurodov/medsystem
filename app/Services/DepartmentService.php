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

            $department->contact->create($validated);
            $department->bed->create($validated);
            $department->employee->create($validated);
            $department->license->create($validated);

            $department->service->sync($validated['services']);

            return $department;
        });
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Department $department, array $validated)
    {
        return DB::transaction(function () use ($validated, $department) {

            $department->update($validated);
            $validated['department_id'] = $department->id;

            $department->contact->create($validated);
            $department->bed->create($validated);
            $department->employee->create($validated);
            $department->license->create($validated);

            $department->service->sync($validated['services']);

            return $department;
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
