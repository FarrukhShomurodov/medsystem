<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\ExaminationMapRequest;
use App\Models\AnalyzeMethod;
use App\Models\AnalyzeType;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\ExaminationMap;
use App\Models\Laboratory;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\Procedure;
use App\Models\Service;
use App\Models\ServiceStaff;
use App\Models\Staff;
use App\Services\ExaminationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ExaminationMapController
{
    protected ExaminationService $service;

    public function __construct(ExaminationService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $maps = ExaminationMap::query()
            ->select(['id', 'patient_id', 'department_id', 'staff_id'])
            ->with('patient')
            ->with('department')
            ->with('doctor')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.maps.index', compact('maps'));
    }

    public function show(ExaminationMap $map): View
    {
        $mapDetails = [
            'id' => $map->id,
            'patient' => [
                'id' => $map->patient->id,
                'full_name' => $map->patient->first_name . ' ' . $map->patient->last_name . ' ' . $map->patient->middle_name,
            ],
            'doctor' => [
                'id' => $map->doctor->id,
                'name' => $map->doctor->name . ' ' . $map->doctor->last_name . ' ' . $map->doctor->middle_name,
                'speciality' => $map->doctor->professionalData->name,
            ],
            'institution' => [
                'name' => $map->department->name,
                'address' => $map->department->address,
                'contacts' => $map->department->phone,
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
                'analyzes' => $map->analyze->map(function ($analyze) {
                    return [
                        'id' => $analyze->id,
                        'type' => $analyze->type,
                        'date' => $analyze->created_at->format('d.m.Y'),
                        'results' => $analyze->results,
                    ];
                }),
            ],
            'created_at' => $map->created_at->format('d.m.Y'),
        ];

        return view('admin.maps.show', compact('mapDetails'));
    }


    public function create(): View
    {
        $doctors = Staff::whereHas('professionalData', function ($query) {
            $query->where('position_id', 1);
        })->get();
        $institutions = Department::query()->get();
        $patients = Patient::query()->get();
        $laboratories = Laboratory::query()->get();
        $types = AnalyzeType::query()->get();
        $equipments = Equipment::query()->where('status', 'работает')->get();
        $methods = AnalyzeMethod::query()->get();

        //service
        $procedures = Procedure::query()->get();
        $medications = Medication::query()->get();
        $staffs = Staff::query()->get();
        $services = Service::query()->get();

        return view('admin.maps.create', compact('doctors', 'institutions', 'patients', 'laboratories', 'types', 'equipments', 'methods', 'procedures', 'medications', 'staffs',
            'services'));
    }

    public function store(ExaminationMapRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('maps.index')->with('success', 'Карта обследования успешно добавлена!');
    }

    public function edit(ExaminationMap $map): View
    {
        $doctors = Staff::whereHas('professionalData', function ($query) {
            $query->where('position_id', 1);
        })->get();
        $institutions = Department::query()->get();
        $patients = Patient::query()->get();
        $laboratories = Laboratory::query()->get();
        $types = AnalyzeType::query()->get();
        $equipments = Equipment::query()->where('status', 'работает')->get();
        $methods = AnalyzeMethod::query()->get();

        //service
        $procedures = Procedure::query()->get();
        $medications = Medication::query()->get();
        $staffs = ServiceStaff::query()->get();
        $services = Service::query()->get();

        return view('admin.maps.edit', compact('map', 'doctors', 'institutions', 'patients', 'laboratories', 'types', 'equipments', 'methods', 'procedures', 'medications', 'staffs',
            'services'));
    }

    public function update(ExaminationMapRequest $request, ExaminationMap $map): RedirectResponse
    {
        $validated = $request->validated();

        $requiredFields = ['laboratory_id', 'types', 'methods', 'analysis_time', 'equipments'];

        $anyFieldFilled = collect($requiredFields)->contains(fn($field) => !empty($validated[$field]));

        $allFieldsFilled = collect($requiredFields)->every(fn($field) => !empty($validated[$field]));

        if ($anyFieldFilled && !$allFieldsFilled) {
            return redirect()->back()->withErrors('Заполните все обязательные поля, кроме медицинского персонала, или не заполняйте их вообще!');
        }


        $this->service->update($map, (array)$validated);

        return redirect()->route('maps.index')->with('success', 'Карта обследования  успешно обновлена!');
    }

    public function destroy(ExaminationMap $map): RedirectResponse
    {
        $this->service->destroy($map);

        return redirect()->route('maps.index')->with('success', 'Карта обследования успешно удалена!');
    }
}
