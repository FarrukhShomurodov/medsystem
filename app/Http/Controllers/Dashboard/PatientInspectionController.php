<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientInspectionRequest;
use App\Services\PatientInspectionService;
use Illuminate\Http\RedirectResponse;

class PatientInspectionController extends Controller
{
    protected PatientInspectionService $service;

    public function __construct(PatientInspectionService $service)
    {
//        $this->middleware(['permission:view_patient_services'])->only('index');
//        $this->middleware(['permission:create_patient_services'])->only(['create', 'store']);
//        $this->middleware(['permission:edit_patient_services'])->only(['edit', 'update']);
//        $this->middleware(['permission:delete_patient_services'])->only('destroy');

        $this->service = $service;
    }

    public function store(PatientInspectionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->back()->with('success', 'Осмотр добавлен!');
    }
}
