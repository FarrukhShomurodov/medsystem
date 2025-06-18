<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientMedicationRequest;
use App\Services\PatientMedicationService;

class PatientMedicationController extends Controller
{
    protected PatientMedicationService $service;

    public function __construct(PatientMedicationService $service)
    {
        $this->service = $service;
    }

    function store(PatientMedicationRequest $request)
    {
        $validated = $request->validated();

        $this->service->store($validated);

        return redirect()->back();
    }
}
