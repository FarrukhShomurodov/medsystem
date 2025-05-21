<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    public function getByType(ServiceType $type): JsonResponse
    {
        $query = Service::query();
        $query->where('service_type_id', $type->id)->where('is_active', true);

        if ($type->id == 4) {
            $query->where('is_appointment', true);
        }

        $services = $query->get();

        return response()->json($services);
    }
}
