<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Http\Requests\ReceptionRequest;
use App\Models\Reception;
use App\Models\Staff;
use App\Services\ReceptionService;
use App\Traits\HandlesStaffAvailability;

class ReceptionController
{
    use HandlesStaffAvailability;

    protected ReceptionService $service;

    public function __construct(ReceptionService $service)
    {
        $this->service = $service;
    }

    public function staffByService(int $serviceId)
    {
        $staff = Staff::whereHas('services', function ($query) use ($serviceId) {
            $query->where('staff_service_id', $serviceId);
            $query->where('is_appointment', true);
        })->with('professionalData')->get();

        return $staff->map(function ($s) {
            return [
                'id' => $s->id,
                'first_name' => $s->name,
                'last_name' => $s->last_name,
                'admission_period' => $s->professionalData->admission_period,
            ];
        });
    }

    public function show(Reception $reception)
    {
        $reception->load('map', 'map.patient', 'staff', 'staff.professionalData', 'staff.professionalData.position');

        return response()->json($reception);
    }

    public function store(ReceptionRequest $request)
    {
        $validated = $request->validated();

        $result = $this->service->store($validated);

        return response()->json([$result, 200]);
    }
}
