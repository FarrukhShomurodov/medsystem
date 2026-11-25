<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Exceptions\Stock\StockException;
use App\Http\Controllers\Controller;
use App\Models\PatientMedication;
use App\Services\Clinical\PatientMedicationService;
use Closure;
use Illuminate\Http\JsonResponse;

/**
 * The "Отменить" and "Продлить" buttons of the prescription list on the patient pages.
 */
class PatientMedicationController extends Controller
{
    public function __construct(private readonly PatientMedicationService $service) {}

    public function cancel(PatientMedication $medication): JsonResponse
    {
        return $this->respond(fn (): PatientMedication => $this->service->cancel($medication));
    }

    public function extend(PatientMedication $medication): JsonResponse
    {
        return $this->respond(fn (): PatientMedication => $this->service->extend($medication));
    }

    /**
     * @param  Closure(): PatientMedication  $action
     */
    private function respond(Closure $action): JsonResponse
    {
        try {
            $prescription = $action();
        } catch (StockException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'id' => $prescription->id,
            'status' => $prescription->status,
            'status_label' => $prescription->status->label(),
            'date_of_end' => $prescription->date_of_end,
        ]);
    }
}
