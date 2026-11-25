<?php

namespace App\Http\Controllers\Dashboard;

use App\Exceptions\Clinical\AnsweredQuestionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\SurveyRequest;
use App\Models\Survey;
use App\Models\SurveyType;
use App\Services\SurveyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SurveyController extends Controller implements HasMiddleware
{
    public function __construct(private readonly SurveyService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_surveys', only: ['index']),
            new Middleware('permission:view_surveys', only: ['show']),
            new Middleware('permission:create_surveys', only: ['create', 'store']),
            new Middleware('permission:edit_surveys', only: ['edit', 'update']),
            new Middleware('permission:delete_surveys', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $surveys = Survey::query()
            ->select(['id', 'title', 'color', 'survey_type_id'])
            ->with(['questions', 'type'])
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.surveys.index', compact('surveys'));
    }

    public function show(Survey $survey): View
    {
        $survey->load(['type', 'questions.group']);

        return view('admin.surveys.show', compact('survey'));
    }

    public function create(): View
    {
        $types = SurveyType::query()->get();

        return view('admin.surveys.create', compact('types'));
    }

    public function store(SurveyRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('surveys.index')->with('success', 'Опросник успешно добавлен!');
    }

    public function edit(Survey $survey): View
    {
        $survey->load(['groups.questions', 'questions']);
        $types = SurveyType::query()->get();

        return view('admin.surveys.edit', compact('survey', 'types'));
    }

    public function update(SurveyRequest $request, Survey $survey): RedirectResponse
    {
        try {
            $this->service->update($survey, $request->validated());
        } catch (AnsweredQuestionException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return to_route('surveys.index')->with('success', 'Опросник успешно обновлен!');
    }

    public function destroy(Survey $survey): RedirectResponse
    {
        $this->service->destroy($survey);

        return to_route('surveys.index')->with('success', 'Опросник успешно удален!');
    }
}
