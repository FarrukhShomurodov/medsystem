<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SelectedDiagnosisRequest;
use App\Services\AdminService;
use Illuminate\Http\JsonResponse;

class SelectedDiagnosisController extends Controller
{
    protected AdminService $service;

    public function __construct(AdminService $service)
    {
        $this->service = $service;
    }

    public function store(SelectedDiagnosisRequest $request): JsonResponse
    {
        $this->service->selectedDiagnosis((array)$request->validated());

        return response()->json('Диагноз добавлен!', 200,);
    }
}
