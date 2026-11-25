<?php

namespace App\Queries\Patients;

use App\Models\Admin;
use App\Models\Department;
use App\Models\Document;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\Procedure;
use App\Models\Service;
use App\Models\Survey;
use App\Models\VitalParameter;
use App\Queries\Billing\RegistrarBasketQuery;
use App\Services\Billing\CurrencyTotals;
use App\Services\Clinical\DangerousCombinationCheck;
use Illuminate\Database\Eloquent\Collection;

/**
 * Everything the patient card (admin.patients.show) renders, loaded with a fixed number of queries.
 */
class PatientCardQuery
{
    public function __construct(
        private readonly PatientTimeline $timeline,
        private readonly RegistrarBasketQuery $basket,
        private readonly DangerousCombinationCheck $combinations,
        private readonly DiagnosisPickerQuery $diagnoses,
    ) {}

    /**
     * @return array<string, mixed> the view data of admin.patients.show
     */
    public function forViewer(Patient $patient, Admin $viewer): array
    {
        $patient->load(['vitalNormalParameters', 'diagnoses' => fn ($query) => $query->with('diagnosis:id,code,name')->latest()]);

        $prescriptions = $this->prescriptions($patient);
        // The diagnosis picker renders the viewer's favourites; it searches the rest of the
        // catalogue through api.diagnoses.search. Only staff who may add a diagnosis have it.
        $favoriteDiagnoses = $viewer->can('create_patient_diagnoses') ? $this->diagnoses->favorites($viewer) : new Collection;
        $basket = $this->basket->items($patient);
        $activeMap = $patient->activeExaminationMap()->first(['id', 'patient_id', 'department_id']);

        return [
            'patient' => $patient,
            'patientDetails' => $this->details($patient),
            'hasActiveMap' => $activeMap !== null,
            // The department the patient lies in: the card's way back is that department's list.
            'activeDepartmentId' => $activeMap?->department_id,
            'timeline' => $this->timeline->for($patient),
            'lastViews' => $this->lastViews($viewer),
            'normalVitalValues' => $patient->vitalNormalParameters->pluck('pivot.normal', 'id'),
            'vitalParameters' => VitalParameter::query()->orderBy('id')->get(['id', 'name']),
            'prescriptions' => $prescriptions,
            'warnings' => $this->combinations->forPatient($patient),
            'medications' => $this->prescribableMedications(),
            'procedures' => Procedure::query()->orderBy('name')->get(['id', 'name']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'documents' => Document::query()
                ->whereKeyNot(Document::INITIAL_NURSING_ASSESSMENT)
                ->orderBy('id')
                ->get(['id', 'title']),
            'favoriteDiagnoses' => $favoriteDiagnoses,
            // The registrar card: what "Подтвердить" confirms and the Excel export lists.
            'allRecords' => $basket,
            'total' => CurrencyTotals::of($basket)->format(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function details(Patient $patient): array
    {
        return [
            'patient_id' => $patient->id,
            'basic_info' => [
                'full_name' => $patient->full_name,
                'birth_date' => $patient->birth_date,
                'gender' => $patient->gender->label(),
                'citizenship' => $patient->citizenship,
                'referral_source' => $patient->referral_source,
            ],
            'contact_info' => [
                'phone' => $patient->phone,
                'additional_phone' => $patient->additional_phone,
                'email' => $patient->email,
                'residential_address' => $this->address(
                    $patient->residential_address_street, $patient->residential_address_house, $patient->residential_address_apartment,
                ),
                'registered_address' => $this->address(
                    $patient->registered_address_street, $patient->registered_address_house, $patient->registered_address_apartment,
                ),
            ],
            'insurance_info' => [
                'policy_number' => $patient->insurance_policy_number ?? 'Не указана',
                'company' => $patient->insurance_company ?? 'Не указана',
                'type' => $patient->insurance_type ?? 'Не указана',
                'expiry_date' => $patient->insurance_expiry_date,
            ],
            'emergency_contact' => [
                'full_name' => $patient->emergency_contact_full_name,
                'relation' => $patient->emergency_contact_relation,
                'phone' => $patient->emergency_contact_phone,
                'address' => $patient->emergency_contact_address,
            ],
            'diagnoses' => $patient->diagnoses,
            // The service and medical document pickers of the referral and document modals.
            'services' => Service::query()->orderBy('name')->get(['id', 'name']),
            'surveys' => Survey::query()->orderBy('title')->get(['id', 'title', 'color']),
        ];
    }

    private function address(?string $street, ?string $house, ?string $apartment): string
    {
        return collect([$street, $house, filled($apartment) ? "квартира {$apartment}" : null])
            ->filter(fn (?string $part): bool => filled($part))
            ->implode(', ');
    }

    /**
     * The other patients this account opened recently, with the stay their list rows show.
     *
     * @return Collection<int, Patient>
     */
    private function lastViews(Admin $viewer): Collection
    {
        return $viewer->lastViewed()
            ->with(['latestExaminationMap.department:id,name', 'latestExaminationMap.doctor:id,name,last_name,middle_name'])
            ->orderByPivot('updated_at', 'desc')
            ->get();
    }

    /**
     * The prescription sheet: every medication ever prescribed, inpatient stays included.
     *
     * @return Collection<int, Medication>
     */
    private function prescriptions(Patient $patient): Collection
    {
        return $patient->prescribedMedications()
            ->withPivot([
                'id', 'dosage', 'admission_period', 'single_dose', 'status', 'method',
                'admission_rules', 'medication_notes', 'date_of_end', 'created_at',
            ])
            ->orderByPivot('created_at')
            ->get();
    }

    /**
     * @return Collection<int, Medication>
     */
    private function prescribableMedications(): Collection
    {
        return Medication::query()->inMainWarehouseStock()->with('composition')->orderBy('name')->get();
    }
}
