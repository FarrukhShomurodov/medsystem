<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientAssignmentRequest;
use App\Services\PatientAssignmentService;
use Illuminate\Http\RedirectResponse;

class PatientAssignmentController extends Controller
{
    protected PatientAssignmentService $service;

    public function __construct(PatientAssignmentService $service)
    {
        $this->service = $service;
    }

    public function store(PatientAssignmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['staff_id'] = auth('admin')->user()->staff_id;

        $this->service->store($validated);

        return redirect(route('patients.show', $validated['patient_id']))->with('success', 'Направление успешно назначена!');
    }
}
