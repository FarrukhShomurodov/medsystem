<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    /**
     * The active services of a type for the order form of the patient card.
     */
    public function getByType(ServiceType $type): JsonResponse
    {
        $services = Service::query()
            ->whereBelongsTo($type, 'type')
            ->where('is_active', true)
            ->when($type->id === ServiceType::INSTRUMENTAL_STUDIES_ID, fn ($query) => $query->where('is_appointment', true))
            ->get();

        return response()->json($services);
    }
}
