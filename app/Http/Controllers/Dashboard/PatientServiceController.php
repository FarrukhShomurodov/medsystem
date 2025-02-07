<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\PatientServiceRequest;
use App\Models\Doctor;
use App\Models\Institution;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\PatientService;
use App\Models\Procedure;
use App\Models\Service;
use App\Models\ServiceEquipment;
use App\Models\ServiceMethod;
use App\Models\ServiceStaff;
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
        $doctors = Doctor::query()->get();
        $institutions = Institution::query()->get();
        $methods = ServiceMethod::query()->get();
        $equipments = ServiceEquipment::query()->get();
        $staffs = ServiceStaff::query()->get();
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
        $doctors = Doctor::query()->get();
        $institutions = Institution::query()->get();
        $methods = ServiceMethod::query()->get();
        $equipments = ServiceEquipment::query()->get();
        $staffs = ServiceStaff::query()->get();
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
