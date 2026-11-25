<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Http\Controllers\Controller;
use App\Models\ExaminationMap;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;

class ExaminationMapController extends Controller
{
    /**
     * The open stay of a patient (at most one), as the list the stay picker of the patient service
     * forms renders: empty for an outpatient.
     */
    public function mapByPatient(Patient $patient): JsonResponse
    {
        $maps = $patient->examinationMaps()
            ->active()
            ->get()
            ->map(fn (ExaminationMap $map): array => [
                'id' => $map->id,
                'patient_name' => $patient->full_name,
                'admission_date' => $map->admission_date,
                'department_name' => $map->department?->name,
                'diagnosis' => $map->admission_diagnosis_description,
            ]);

        return response()->json($maps);
    }
}
