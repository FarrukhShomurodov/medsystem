<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Models\Medication;
use Illuminate\Http\JsonResponse;

class MedicationController
{
    public function show(Medication $medication): JsonResponse
    {
        return response()->json($medication);
    }
}
