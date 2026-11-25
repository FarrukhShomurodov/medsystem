<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\SurveyResponseRequest;
use App\Models\ExaminationMap;
use App\Models\Survey;
use App\Services\SurveyResponseService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SurveyResponseController extends Controller
{
    public function __construct(private readonly SurveyResponseService $service) {}

    public function create(ExaminationMap $map): View
    {
        $map->load(['patient', 'surveyResponses' => fn ($responses) => $responses->latest('id')->limit(5)->with('survey')]);
        $surveys = Survey::query()->orderBy('title')->get(['id', 'title']);

        return view('admin.maps.surveys', compact('map', 'surveys'));
    }

    public function store(SurveyResponseRequest $request): RedirectResponse
    {
        $this->service->store($request->surveyResponse());

        return back()->with('success', 'Опросник заполнен!');
    }
}
