<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientDiagnosisRequest;
use App\Services\PatientDiagnosisService;

class PatientDiagnosisController extends Controller
{
    protected PatientDiagnosisService $service;

    public function __construct(PatientDiagnosisService $service)
    {
        $this->service = $service;
    }


    public function store(PatientDiagnosisRequest $request)
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('patients.show', $validated['patient_id'])
            ->with('success', 'Диагноз успешно добавлен!');
    }

}
