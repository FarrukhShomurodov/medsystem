<?php

namespace App\Services;

use App\Models\PatientAssignment;

class PatientAssignmentService
{
    public function store(array $validated)
    {
        return PatientAssignment::query()->create($validated);
    }
}
