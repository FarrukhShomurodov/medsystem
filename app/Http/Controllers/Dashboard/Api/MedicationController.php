<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Models\Medication;
use Illuminate\Http\JsonResponse;

class MedicationController
{
    public function show(Medication $medication): JsonResponse
    {
        $medication->load(['group', 'composition']);
        return response()->json($medication);
    }

    public function medicationByActiveSubstance(string $substance): JsonResponse
    {
        $medication = Medication::query()->with('composition')->whereHas('composition', function ($q) use ($substance) {
            $q->where('active_substance_name', $substance);
        })->get();

        return response()->json($medication);
    }
}
