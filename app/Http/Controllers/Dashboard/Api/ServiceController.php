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
        $services = Service::query()->where('service_type_id', $type->id)->where('is_active', true);

        if ($type->id == 4) {
            $services->where('is_appointment', true);
        }

        $services->get();

        return response()->json($services);
    }
}
