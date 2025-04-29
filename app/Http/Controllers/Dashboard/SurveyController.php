<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\SurveyRequest;
use App\Models\Survey;
use App\Models\SurveyType;
use App\Services\SurveyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SurveyController extends Controller
{
    protected SurveyService $service;

    public function __construct(SurveyService $service)
    {
        $this->middleware(['permission:view_surveys'])->only('index');
        $this->middleware(['permission:create_surveys'])->only(['create', 'store']);
        $this->middleware(['permission:edit_surveys'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_surveys'])->only('destroy');

        $this->service = $service;
    }

    public function index(): View
    {
        $surveys = Survey::query()
            ->select(['id', 'title', 'color', 'survey_type_id'])
            ->with('questions')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.surveys.index', compact('surveys'));
    }

    public function show(Survey $survey): View
    {
        return view('admin.surveys.show', compact('survey'));
    }

    public function create(): View
    {
        $types = SurveyType::query()->get();
        return view('admin.surveys.create', compact('types'));
    }

    public function store(SurveyRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('surveys.index')->with('success', 'Опросник успешно добавлен!');
    }

    public function edit(Survey $survey): View
    {
        $types = SurveyType::query()->get();
        return view('admin.surveys.edit', compact('survey', 'types'));
    }

    public function update(SurveyRequest $request, Survey $survey): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($survey, (array)$validated);

        return redirect()->route('surveys.index')->with('success', 'Опросник успешно обновлен!');
    }

    public function destroy(Survey $survey): RedirectResponse
    {
        $this->service->destroy($survey);

        return redirect()->route('surveys.index')->with('success', 'Опросник успешно удален!');
    }
}
