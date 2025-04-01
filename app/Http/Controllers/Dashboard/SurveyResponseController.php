<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Requests\SurveyResponseRequest;
use App\Models\ExaminationMap;
use App\Models\Medication;
use App\Models\Procedure;
use App\Models\Service;
use App\Models\Staff;
use App\Models\Survey;
use App\Services\SurveyResponseService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

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
        $staffs = Staff::query()->get();
        $services = Service::query()->get();

        return view('admin.maps.surveys', compact('map', 'surveys', 'procedures', 'medications', 'staffs', 'services'));
    }

    public function store(SurveyResponseRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->service->store($validated);

        return redirect()->route('maps.index');
    }
}
