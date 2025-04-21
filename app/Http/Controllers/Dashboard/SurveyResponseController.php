<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Requests\SurveyResponseRequest;
use App\Models\ExaminationMap;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\Procedure;
use App\Models\Service;
use App\Models\Staff;
use App\Models\Survey;
use App\Services\SurveyResponseService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class SurveyResponseController
{

    protected SurveyResponseService $service;

    public function __construct(SurveyResponseService $service)
    {
        $this->service = $service;
    }

    public function create(ExaminationMap $map): View
    {
        $surveys = Survey::query()->get();

        $procedures = Procedure::query()->get();
        $medications = Medication::query()->get();

        $today = Carbon::parse($date ?? now());
        $staffs = Staff::with(['absence' => function ($query) {
            $query->latest()->limit(1);
        }])->get()->filter(function ($staff) use ($today) {
            $absence = $staff->absence->first();
            $isAbsent =  $absence && $absence->start_date->lte($today) && $absence->end_date->gte($today);
            return !$isAbsent;
        });

        $services = Service::query()->get();

        return view('admin.maps.surveys', compact('map', 'surveys', 'procedures', 'medications', 'staffs', 'services'));
    }

    public function store(SurveyResponseRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $patientId = ExaminationMap::find($validated['examination_map_id'])->patient->id;
        $this->service->store($validated);

        return redirect()->route('patients.show', $patientId);
    }
}
