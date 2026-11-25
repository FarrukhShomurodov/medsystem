<?php

namespace App\Http\Controllers\Dashboard;

use App\Exceptions\Clinical\PaidServiceException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PatientServiceRequest;
use App\Models\Patient;
use App\Models\PatientService;
use App\Models\Service;
use App\Services\Clinical\PatientServiceAssignService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use LogicException;

class PatientServiceController extends Controller implements HasMiddleware
{
    public function __construct(private readonly PatientServiceAssignService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_patient_services', only: ['index']),
            new Middleware('permission:create_patient_services', only: ['create', 'store']),
            new Middleware('permission:edit_patient_services', only: ['edit', 'update']),
            new Middleware('permission:delete_patient_services', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $services = PatientService::query()
            ->select(['id', 'service_id', 'patient_id', 'service_date', 'status'])
            ->with(['service', 'patient'])
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.patient_services.index', compact('services'));
    }

    public function create(): View
    {
        $catalogue = Service::query()->where('is_active', true)->get();
        $selectedPatient = $this->selectedPatient(null);

        return view('admin.patient_services.create', compact('catalogue', 'selectedPatient'));
    }

    public function store(PatientServiceRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        // Ordered from the patient card: stay there.
        if (Str::contains(url()->previous(), 'dashboard/patients/')) {
            return back()->with('success', 'Услуга назначена!');
        }

        return to_route('patient.services.index')->with('success', 'Услуга назначена!');
    }

    public function edit(PatientService $service): View
    {
        $catalogue = Service::query()->where('is_active', true)->get();
        $selectedPatient = $this->selectedPatient($service);
        // The map options print the patient's name; it is the same patient for all of them.
        $patient = $service->patient ?? throw new LogicException("Patient service {$service->id} has no patient.");
        $maps = $patient->examinationMaps->each->setRelation('patient', $patient);

        return view('admin.patient_services.edit', compact('catalogue', 'selectedPatient', 'service', 'maps'));
    }

    public function update(PatientServiceRequest $request, PatientService $service): RedirectResponse
    {
        try {
            $this->service->update($service, $request->validated());
        } catch (PaidServiceException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return to_route('patient.services.index')->with('success', 'Назначенная услуга успешно обновлена!');
    }

    public function destroy(PatientService $service): RedirectResponse
    {
        try {
            $this->service->destroy($service);
        } catch (PaidServiceException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return to_route('patient.services.index')->with('success', 'Назначенная услуга успешно удалена!');
    }

    /**
     * The patient the form shows as picked: the one of a refused submission, else the order's.
     * The field searches the register through api.patients.index (PatientPickerQuery), so this is
     * the only patient the page prints.
     */
    private function selectedPatient(?PatientService $service): ?Patient
    {
        // Old input comes back from a refused submission and can be any string, or an array.
        $id = filter_var(old('patient_id') ?? $service?->patient_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? null : Patient::query()->find($id, ['id', 'first_name', 'last_name', 'middle_name', 'birth_date']);
    }
}
