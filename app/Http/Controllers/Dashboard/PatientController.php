<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\PatientRequest;
use App\Models\Allegory;
use App\Models\ChronicDisease;
use App\Models\City;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Institution;
use App\Models\MedicalDevice;
use App\Models\Patient;
use App\Models\Region;
use App\Models\Staff;
use App\Services\PatientService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PatientController
{
    protected PatientService $service;

    public function __construct(PatientService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $patients = Patient::query()
            ->select(['id', 'first_name', 'last_name', 'birth_date', 'gender', 'citizenship', 'passport_series'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.patients.index', compact('patients'));
    }

    public function show(Patient $patient): View
    {
        $patientDetails = [
            'basic_info' => [
                'full_name' => "{$patient->last_name} {$patient->first_name} {$patient->middle_name}",
                'birth_date' => $patient->birth_date,
                'gender' => $patient->gender === 'male' ? 'Мужской' : 'Женский',
                'citizenship' => $patient->citizenship,
            ],
            'contact_info' => [
                'phone' => $patient->phone,
                'additional_phone' => $patient->additional_phone,
                'email' => $patient->email,
                'residential_address' => "{$patient->residential_address_street}, {$patient->residential_address_house}, квартира {$patient->residential_address_apartment}",
                'registered_address' => "{$patient->registered_address_street}, {$patient->registered_address_house}, квартира {$patient->registered_address_apartment}",
            ],
            'insurance_info' => [
                'policy_number' => $patient->insurance_policy_number ?? 'Не указана',
                'company' => $patient->insurance_company ?? 'Не указана',
                'type' => $patient->insurance_type ?? 'Не указана',
                'expiry_date' => $patient->insurance_expiry_date ?? 'Не указана',
            ],
            'emergency_contact' => [
                'full_name' => $patient->emergency_contact_full_name,
                'relation' => $patient->emergency_contact_relation,
                'phone' => $patient->emergency_contact_phone,
                'address' => $patient->emergency_contact_address,
            ],
            'medical_info' => [
                'blood_type' => $patient->blood_type,
                'rh_factor' => $patient->rh_factor ? ($patient->rh_factor === '+' ? 'Положительный' : 'Отрицательный') : 'Не указан',
                'special_instructions' => $patient->special_instructions,
            ],
            'related_data' => [
                'allegories' => $patient->allegories,
                'devices' => $patient->devices,
                'diseases' => $patient->diseases,
                'doctors' => $patient->doctors,
                'examination_maps' => $patient->examinationMaps,
            ],
            'history' => [
                'maps' => $patient->examinationMaps,
            ],
            'patient_id' => $patient->id
        ];

        return view('admin.patients.show', compact('patientDetails'));
    }

    public function create(): View
    {
        $regions = Region::query()->get();
        $cities = City::query()->get();
        $doctors = Staff::whereHas('professionalData', function ($query) {
            $query->where('position_id', 1);
        })->get();
        $institutions = Department::query()->get();
        $allegories = Allegory::query()->get();
        $devices = MedicalDevice::query()->get();
        $diseases = ChronicDisease::query()->get();

        return view('admin.patients.create', compact('regions', 'cities', 'doctors', 'institutions', 'allegories', 'devices', 'diseases'));
    }

    public function store(PatientRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('patients.index')->with('success', 'Пациент устройство успешно добавлена!');
    }

    public function edit(Patient $patient): View
    {
        $regions = Region::query()->get();
        $cities = City::query()->get();
        $doctors = Staff::whereHas('professionalData', function ($query) {
            $query->where('position_id', 1);
        })->get();
        $institutions = Department::query()->get();
        $allegories = Allegory::query()->get();
        $devices = MedicalDevice::query()->get();
        $diseases = ChronicDisease::query()->get();
        $maps = $patient->examinationMaps;

        return view('admin.patients.edit', compact('patient', 'regions', 'cities', 'doctors', 'institutions', 'allegories', 'devices', 'diseases', 'maps'));
    }

    public function update(PatientRequest $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validated();

        if (
            (!empty($validated['institution_id']) && empty($validated['staff_id']) && empty($validated['admission_date'])) ||
            (!empty($validated['staff_id']) && empty($validated['institution_id']) && empty($validated['admission_date'])) ||
            (!empty($validated['admission_date']) && empty($validated['institution_id']) && empty($validated['staff_id']))
        ) {
            return redirect()->back()->withErrors('Заполните все поля для добавления обращения или не заполняйте их вообще!');
        }

        $this->service->update($patient, (array)$validated);

        return redirect()->route('patients.index')->with('success', 'Пациент устройство  успешно обновлена!');
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        $this->service->destroy($patient);

        return redirect()->route('patients.index')->with('success', 'Пациент устройство успешно удалена!');
    }
}
