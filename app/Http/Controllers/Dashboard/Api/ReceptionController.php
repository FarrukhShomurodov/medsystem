<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReceptionRequest;
use App\Models\Reception;
use App\Models\Staff;
use App\Services\ReceptionService;
use App\Traits\HandlesStaffAvailability;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReceptionController extends Controller
{
    use HandlesStaffAvailability;

    protected ReceptionService $service;

    public function __construct(ReceptionService $service)
    {
        $this->middleware(['permission:create_receptions'])->only(['create']);
        $this->middleware(['permission:change_receptions_status'])->only(['changeStatus']);
        $this->service = $service;
    }

    public function staffByService(int $serviceId)
    {
        $staff = Staff::whereHas('services', function ($query) use ($serviceId) {
            $query->where('staff_service_id', $serviceId);
            $query->where('is_appointment', true);
        })->get();

//        ->whereHas('professionalData', function ($q) {
//        $q->where('position_id', 1);
//    })

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
        $reception->load(
            'patient',
            'map',
            'map.patient',
            'staff',
            'staff.professionalData',
            'staff.professionalData.position'
        );

        return response()->json($reception);
    }

    public function store(ReceptionRequest $request)
    {
        $validated = $request->validated();

        $result = $this->service->store($validated);

        return response()->json([$result, 200]);
    }

    public function changeStatus(Reception $reception, Request $request)
    {
        $validated = $request->validate(['status' => ['required', Rule::in(Reception::STATUS)]]);

        $result = $this->service->update($reception, $validated);

        return response()->json([$result, 200]);
    }
}
