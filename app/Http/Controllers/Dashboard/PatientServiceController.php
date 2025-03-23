<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\PatientServiceRequest;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\PatientService;
use App\Models\Procedure;
use App\Models\Service;
use App\Models\ServiceMethod;
use App\Models\Staff;
use App\Services\PatientServiceAssignService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PatientServiceController
{
    protected PatientServiceAssignService $service;

    public function __construct(PatientServiceAssignService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $services = PatientService::query()
            ->select(['id', 'service_id', 'patient_id', 'service_date', 'status'])
            ->with('service')
            ->with('patient')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.patient_services.index', compact('services'));
    }

    public function show()
    {
        dd('show');
    }

    public function create(): View
    {
        $services = Service::query()->get();
        $patients = Patient::query()->get();
        $doctors = Staff::whereHas('professionalData', function ($query) {
            $query->where('position_id', 1);
        })->get();
        $institutions = Department::query()->get();
        $methods = ServiceMethod::query()->get();
        $equipments = Equipment::query()->where('status', 'работает')->get();
        $staffs = Staff::query()->get();
        $procedures = Procedure::query()->get();
        $medications = Medication::query()->get();

        return view('admin.patient_services.create', compact('services', 'patients', 'doctors', 'institutions', 'methods', 'equipments', 'staffs', 'procedures', 'medications'));
    }

    public function store(PatientServiceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('patient.services.index')->with('success', 'Услуга назначена!');
    }

    public function edit(PatientService $service): View
    {
        $maintanances = Service::query()->get();
        $patients = Patient::query()->with('examinationMaps')->get();
        $doctors = Staff::whereHas('professionalData', function ($query) {
            $query->where('position_id', 1);
        })->get();
        $institutions = Department::query()->get();
        $methods = ServiceMethod::query()->get();
        $equipments = Equipment::query()->where('status', 'работает')->get();
        $staffs = Staff::query()->get();
        $service->with('methods');
        $maps = $service->patient->examinationMaps;
        $procedures = Procedure::query()->get();
        $medications = Medication::query()->get();

        return view('admin.patient_services.edit', compact('maintanances', 'patients', 'doctors', 'institutions', 'methods', 'equipments', 'staffs', 'service', 'maps', 'procedures', 'medications'));
    }

    public function update(PatientServiceRequest $request, PatientService $service): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($service, (array)$validated);

        return redirect()->route('patient.services.index')->with('success', 'Назначеная услуга  успешно обновлена!');
    }

    public function destroy(PatientService $service): RedirectResponse
    {
        $this->service->destroy($service);

        return redirect()->route('patient.services.index')->with('success', 'Назначеная услуга успешно удалена!');
    }
}
