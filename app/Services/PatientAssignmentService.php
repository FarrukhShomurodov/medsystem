<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\PatientAssignment;

class PatientAssignmentService
{
    /**
     * @param  array{patient_id: int, service_id: int, comment?: string|null}  $validated
     */
    public function store(array $validated, Admin $author): PatientAssignment
    {
        return PatientAssignment::query()->create([
            ...$validated,
            'staff_id' => $author->staff_id,
        ]);
    }
}
