<?php

namespace App\Http\Controllers\Dashboard;

use App\Exceptions\Patients\ActiveExaminationMapException;
use App\Exceptions\Patients\DeletionRefusedException;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ExaminationMapIsActive;
use App\Http\Requests\ExaminationMapRequest;
use App\Models\Department;
use App\Models\Document;
use App\Models\ExaminationMap;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Services\Patients\ExaminationMapService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use LogicException;

class ExaminationMapController extends Controller implements HasMiddleware
{
    public function __construct(private readonly ExaminationMapService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:create_patient_map', only: ['create', 'store']),
            new Middleware('permission:view_patients|edit_patients', only: ['show']),
            new Middleware('permission:edit_maps', only: ['edit']),
            new Middleware('permission:update_maps', only: ['update']),
            new Middleware('permission:delete_maps', only: ['destroy']),
            // After the permission check, so the map state is only revealed to staff who may act on it.
            // A closed stay is a medical record: it can be viewed, not edited or deleted.
            ExaminationMapIsActive::class,
        ];
    }

    public function show(ExaminationMap $map): View
    {
        $map->load([
            'patient:id,first_name,last_name,middle_name',
            'doctor.professionalData.speciality:id,name',
            'department:id,name',
            'services',
            'surveyResponses.survey:id,title',
        ]);

        return view('admin.maps.show', ['mapDetails' => $this->details($map)]);
    }

    public function create(Patient $patient): View
    {
        return view('admin.maps.create', [
            'patient' => $patient,
            'initialAssessment' => $patient->pendingInitialAssessment(),
            'doctors' => $this->doctorsOnDuty(),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(ExaminationMapRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $this->service->store($validated);
        } catch (ActiveExaminationMapException $exception) {
            return to_route('patients.show', $validated['patient_id'])->with('error', $exception->getMessage());
        }

        return to_route('patients.show', $validated['patient_id'])->with('success', 'Карта обследования успешно добавлена!');
    }

    public function edit(ExaminationMap $map): View
    {
        return view('admin.maps.edit', [
            'map' => $map,
            'initialAssessment' => $map->documentResponses()
                ->where('document_id', Document::INITIAL_NURSING_ASSESSMENT)
                ->latest()
                ->first(),
            'doctors' => $this->doctorsFor($map),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'prescriptions' => $map->patientMedications()
                ->with(['medication.composition', 'medication.group', 'compatibleMedications'])
                ->oldest()
                ->get(),
        ]);
    }

    public function update(ExaminationMapRequest $request, ExaminationMap $map): RedirectResponse
    {
        $this->service->update($map, $request->validated());

        return to_route('patients.show', $map->patient_id)->with('success', 'Карта обследования успешно обновлена!');
    }

    public function destroy(ExaminationMap $map): RedirectResponse
    {
        try {
            $this->service->destroy($map);
        } catch (DeletionRefusedException $exception) {
            return to_route('patients.show', $map->patient_id)->with('error', $exception->getMessage());
        }

        return to_route('patients.show', $map->patient_id)->with('success', 'Карта обследования успешно удалена!');
    }

    /**
     * The doctors a stay can be assigned to: those working today, by surname as the form names them.
     *
     * @return Collection<int, Staff>
     */
    private function doctorsOnDuty(): Collection
    {
        return Staff::query()
            ->workingOn(today())
            ->inPosition(StaffPosition::DOCTOR)
            ->orderBy('last_name')
            ->orderBy('name')
            ->get();
    }

    /**
     * The doctors on duty today plus the attending doctor of the stay: when that doctor is off
     * duty, the select would otherwise fall back to another doctor and saving the form (to
     * discharge the patient, say) would silently reassign the stay.
     *
     * @return Collection<int, Staff>
     */
    private function doctorsFor(ExaminationMap $map): Collection
    {
        $doctors = $this->doctorsOnDuty();

        if ($doctors->contains($map->staff_id)) {
            return $doctors;
        }

        return $doctors->prepend($map->doctor ?? throw new LogicException("Examination map {$map->id} has no attending doctor."));
    }

    /**
     * @return array<string, mixed>
     */
    private function details(ExaminationMap $map): array
    {
        $patient = $map->patient ?? throw new LogicException("Examination map {$map->id} has no patient.");
        $doctor = $map->doctor ?? throw new LogicException("Examination map {$map->id} has no attending doctor.");
        $department = $map->department ?? throw new LogicException("Examination map {$map->id} has no department.");

        return [
            'id' => $map->id,
            'is_outpatient' => $map->is_outpatient,
            'patient' => [
                'id' => $patient->id,
                'full_name' => $patient->full_name,
            ],
            'doctor' => [
                'id' => $doctor->id,
                'name' => $doctor->full_name,
                'speciality' => $doctor->professionalData?->speciality?->name,
            ],
            'department' => [
                'name' => $department->name,
            ],
            'admission' => [
                'date' => $map->admission_date,
                'discharge_date' => $map->discharge_date,
                'admission_diagnosis' => [
                    'code' => $map->admission_diagnosis_code,
                    'description' => $map->admission_diagnosis_description,
                ],
                'discharge_diagnosis' => [
                    'code' => $map->discharge_diagnosis_code,
                    'description' => $map->discharge_diagnosis_description,
                ],
                'purpose' => $map->examination_purpose,
            ],
            'anamnesis' => [
                'complaints' => $map->complaints,
                'disease_history' => $map->disease_history,
                'general_anamnesises' => $map->general_anamnesises,
            ],
            'examinations' => [
                'services' => $map->services,
                'surveyResponses' => $map->surveyResponses,
            ],
            'created_at' => $map->created_at?->format('d.m.Y'),
        ];
    }
}
