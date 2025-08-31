<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientMedicationRequest;
use App\Models\Medication;
use App\Models\PatientMedication;
use App\Services\PatientMedicationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PatientMedicationController extends Controller
{
    protected PatientMedicationService $service;

    public function __construct(PatientMedicationService $service)
    {
        $this->service = $service;
    }

    function store(PatientMedicationRequest $request)
    {
        $validated = $request->validated();

        $this->service->store($validated);

        return redirect()->back();
    }

    public function edit(PatientMedication $medication): View
    {
        $allMedications = Medication::query()->get();

        return view('admin.patients.medication.edit', compact('medication', 'allMedications'));
    }

    public function update(PatientMedicationRequest $request, PatientMedication $medication): RedirectResponse
    {
        $validated = $request->validated();

        $this->service->update($medication, (array)$validated);

        return redirect()->route('patients.show', $medication->patient_id)->with('success', 'Лист назначения успешно обновлен!');
    }
}
