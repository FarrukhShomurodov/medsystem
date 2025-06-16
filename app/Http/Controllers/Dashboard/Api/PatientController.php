<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Models\Patient;
use Illuminate\Http\JsonResponse;

class PatientController
{
    public function get(): JsonResponse
    {
        $patients = Patient::query()->get();
        return response()->json($patients);
    }

    public function show(Patient $patient): JsonResponse
    {
        $patient->load('examinationMaps');
        return response()->json($patient);
    }
}
