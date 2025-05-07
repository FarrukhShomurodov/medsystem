<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Http\Controllers\Controller;
use App\Models\PatientService;
use App\Services\PatientServiceAssignService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientServiceController extends Controller
{
    protected $service;

    public function __construct(PatientServiceAssignService $service)
    {
        $this->service = $service;
    }

    public function changeStatus(PatientService $service, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(PatientService::STATUS)]
        ]);

        $this->service->update($service, $validated);

        return response()->json([], 200);
    }
}
