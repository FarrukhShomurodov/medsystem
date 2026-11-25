<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Exceptions\Clinical\ServiceStatusTransitionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clinical\PatientServiceStatusRequest;
use App\Models\PatientService;
use App\Services\Clinical\PatientServiceAssignService;
use Illuminate\Http\JsonResponse;

class PatientServiceController extends Controller
{
    public function __construct(private readonly PatientServiceAssignService $service) {}

    public function changeStatus(PatientService $service, PatientServiceStatusRequest $request): JsonResponse
    {
        try {
            $service = $this->service->changeStatus($service, $request->status());
        } catch (ServiceStatusTransitionException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['status' => $service->status]);
    }
}
