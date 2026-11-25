<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patients\PatientSearchRequest;
use App\Models\Patient;
use App\Queries\Patients\PatientPickerQuery;
use Illuminate\Http\JsonResponse;

class PatientController extends Controller
{
    /**
     * Patient picker of the reception calendars and of the patient service form: the patients
     * matching the typed text, with only what the select2 widgets render (lib/patient-picker.js).
     */
    public function get(PatientSearchRequest $request, PatientPickerQuery $patients): JsonResponse
    {
        return response()->json($patients->search($request->term())->map(fn (Patient $patient): array => [
            'id' => $patient->id,
            'full_name' => $patient->full_name,
            'birth_date' => $patient->birth_date,
        ]));
    }
}
