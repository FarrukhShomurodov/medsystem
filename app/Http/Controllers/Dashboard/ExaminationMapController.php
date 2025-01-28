<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\ExaminationMapRequest;
use App\Models\Doctor;
use App\Models\ExaminationMap;
use App\Models\Institution;
use App\Models\Patient;
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
            ->select(['id', 'patient_id', 'institution_id', 'doctor_id'])
            ->with('patient')
            ->with('institution')
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
                'name' => $map->doctor->first_name . ' ' . $map->doctor->last_name . ' ' . $map->doctor->middle_name,
                'speciality' => $map->doctor->specialty->name,
            ],
            'institution' => [
                'name' => $map->institution->name,
                'address' => $map->institution->address,
                'contacts' => $map->institution->phone,
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
        $doctors = Doctor::query()->get();
        $institutions = Institution::query()->get();
        $patients = Patient::query()->get();

        return view('admin.maps.create', compact('doctors', 'institutions', 'patients'));
    }

    public function store(ExaminationMapRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('maps.index')->with('success', 'Карта обследования успешно добавлена!');
    }

    public function edit(ExaminationMap $map): View
    {
        $doctors = Doctor::query()->get();
        $institutions = Institution::query()->get();
        $patients = Patient::query()->get();

        return view('admin.maps.edit', compact('map', 'doctors', 'institutions', 'patients'));
    }

    public function update(ExaminationMapRequest $request, ExaminationMap $map): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($map, (array)$validated);

        return redirect()->route('maps.index')->with('success', 'Карта обследования  успешно обновлена!');
    }

    public function destroy(ExaminationMap $map): RedirectResponse
    {
        $this->service->destroy($map);

        return redirect()->route('maps.index')->with('success', 'Карта обследования успешно удалена!');
    }
}
