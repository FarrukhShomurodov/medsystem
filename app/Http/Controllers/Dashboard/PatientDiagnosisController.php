<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientDiagnosisRequest;
use App\Services\PatientDiagnosisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use LogicException;

class PatientDiagnosisController extends Controller
{
    public function __construct(private readonly PatientDiagnosisService $service) {}

    /**
     * The patient card posts it with AJAX and offers the surveys linked to the diagnosis.
     */
    public function store(PatientDiagnosisRequest $request): JsonResponse|RedirectResponse
    {
        $diagnosis = $this->service->store($request->diagnosis());

        if ($request->ajax()) {
            $diagnosis->load('diagnosis.surveys');
            $catalogued = $diagnosis->diagnosis ?? throw new LogicException("Patient diagnosis {$diagnosis->id} has no catalogue diagnosis.");

            return response()->json([
                'status' => true,
                'diagnosis_id' => $diagnosis->id,
                'surveys' => $catalogued->surveys,
            ]);
        }

        return to_route('patients.show', $diagnosis->patient_id)
            ->with('success', 'Диагноз успешно добавлен!');
    }
}
