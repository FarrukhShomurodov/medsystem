<?php

namespace App\Http\Controllers\Dashboard;

use App\Exceptions\Patients\DeletionRefusedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PatientRequest;
use App\Http\Requests\Patients\PatientFilterRequest;
use App\Models\Admin;
use App\Models\City;
use App\Models\Department;
use App\Models\Patient;
use App\Models\Region;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\StaffProfessionalData;
use App\Queries\Patients\PatientCardQuery;
use App\Queries\Patients\PatientListQuery;
use App\Services\Patients\RecordPatientView;
use App\Services\PatientService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PatientController extends Controller implements HasMiddleware
{
    public function __construct(private readonly PatientService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_patients', only: ['index']),
            new Middleware('permission:view_patients', only: ['show']),
            new Middleware('permission:create_patients', only: ['create', 'store']),
            new Middleware('permission:edit_patients', only: ['edit', 'update']),
            new Middleware('permission:delete_patients', only: ['destroy']),
        ];
    }

    public function index(PatientFilterRequest $request, PatientListQuery $patients): View
    {
        return view('admin.patients.index', [
            'patients' => $patients->paginate($request->validated()),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            // The attending doctors of the stays the list can be filtered by, on duty today or not,
            // named in full: two doctors may share a first name.
            'staffs' => Staff::query()
                ->inPosition(StaffPosition::DOCTOR)
                ->orderBy('last_name')
                ->orderBy('name')
                ->get(['id', 'name', 'last_name', 'middle_name']),
        ]);
    }

    public function show(Patient $patient, PatientCardQuery $card, RecordPatientView $recordView, #[CurrentUser(Admin::GUARD)] Admin $viewer): View
    {
        $recordView($viewer, $patient);

        return view('admin.patients.show', $card->forViewer($patient, $viewer));
    }

    public function create(#[CurrentUser(Admin::GUARD)] Admin $viewer): View
    {
        // The front desk registers walk-in patients with the short form; the full card is for the rest.
        $view = $this->worksAtFrontDesk($viewer) ? 'admin.patients.register' : 'admin.patients.create';

        return view($view, $this->addressLookups());
    }

    public function store(PatientRequest $request, #[CurrentUser(Admin::GUARD)] Admin $viewer): RedirectResponse
    {
        $patient = $this->service->store($request->validated());

        return $this->redirectToPatient($patient, $viewer)->with('success', 'Пациент успешно зарегистрирован.');
    }

    public function edit(Patient $patient): View
    {
        return view('admin.patients.edit', [
            'patient' => $patient->load('files'),
            'maps' => $patient->examinationMaps,
            ...$this->addressLookups(),
        ]);
    }

    public function update(PatientRequest $request, Patient $patient, #[CurrentUser(Admin::GUARD)] Admin $viewer): RedirectResponse
    {
        $this->service->update($patient, $request->validated());

        return $this->redirectToPatient($patient, $viewer)->with('success', 'Данные пациента успешно обновлены.');
    }

    public function destroy(Patient $patient, #[CurrentUser(Admin::GUARD)] Admin $viewer): RedirectResponse
    {
        try {
            $this->service->destroy($patient);
        } catch (DeletionRefusedException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $route = $viewer->can('list_patients') ? 'patients.index' : 'dashboard';

        return to_route($route)->with('success', 'Пациент успешно удалён.');
    }

    /**
     * @return array{regions: Collection<int, Region>, cities: Collection<int, City>}
     */
    private function addressLookups(): array
    {
        return [
            'regions' => Region::query()->orderBy('name')->get(['id', 'name']),
            'cities' => City::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * The patient's card for accounts that may open it (the front desk works with cards and may
     * not open the list), else the list, else the form the account came from: a redirect to a
     * page it may not open would greet a successful save with a 403.
     */
    private function redirectToPatient(Patient $patient, Admin $viewer): RedirectResponse
    {
        return match (true) {
            $viewer->can('view_patients') => to_route('patients.show', $patient),
            $viewer->can('list_patients') => to_route('patients.index'),
            default => back(),
        };
    }

    private function worksAtFrontDesk(Admin $admin): bool
    {
        return $admin->staff_id !== null && StaffProfessionalData::query()
            ->where('staff_id', $admin->staff_id)
            ->where('position_id', StaffPosition::REGISTRAR)
            ->exists();
    }
}
