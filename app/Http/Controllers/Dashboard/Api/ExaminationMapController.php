<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Models\Patient;
use Illuminate\Http\JsonResponse;

class ExaminationMapController
{
    public function mapByPatient(Patient $patient): JsonResponse
    {
        $maps = $patient->examinationMaps->map(function ($map) {
            if ($map->is_active) {
                return [
                    'id' => $map->id,
                    'patient_name' => $map->patient->first_name,
                    'admission_date' => $map->admission_date,
                    'institution_name' => $map->department->name,
                    'diagnosis' => $map->admission_diagnosis_description,
                ];
            }
        });

        return response()->json($maps);
    }
}
