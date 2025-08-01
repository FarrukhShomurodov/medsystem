<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Http\Controllers\Controller;
use App\Models\PatientMedication;
use App\Services\PatientMedicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientMedicationController extends Controller
{
    protected PatientMedicationService $service;

    public function __construct(PatientMedicationService $service)
    {
        $this->service = $service;
    }

    public function cancel(PatientMedication $medication): JsonResponse
    {
        $medication = $this->service->cancel($medication);

        return response()->json($medication);
    }
}
