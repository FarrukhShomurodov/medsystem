<?php

namespace App\Services;

use App\Enums\QuestionType;
use App\Exceptions\Clinical\AnsweredQuestionException;
use App\Models\Survey;
use App\Models\SurveyGroup;
use App\Models\SurveyQuestion;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SurveyService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(array $validated): Survey
    {
        return DB::transaction(function () use ($validated): Survey {
            $survey = Survey::query()->create(Arr::only($validated, ['title', 'survey_type_id', 'color']));
            $this->syncStructure($survey, $validated);

            return $survey;
        });
    }

    /**
     * Groups and questions that come back with their id are updated in place: deleting and
     * re-creating them used to cascade-delete every patient answer to the survey.
     *
     * @param  array<string, mixed>  $validated
     *
     * @throws AnsweredQuestionException when the form drops a question patients already answered
     */
    public function update(Survey $survey, array $validated): Survey
    {
        return DB::transaction(function () use ($survey, $validated): Survey {
            $survey->update(Arr::only($validated, ['title', 'survey_type_id', 'color']));
            $this->syncStructure($survey, $validated);

            return $survey;
        });
    }

    public function destroy(Survey $survey): void
    {
        $survey->delete();
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function syncStructure(Survey $survey, array $validated): void
    {
        $keptGroups = [];
        $keptQuestions = [];

        foreach ($validated['groups'] ?? [] as $groupData) {
            $group = $this->saveGroup($survey, $groupData);
            $keptGroups[] = $group->id;

            foreach ($groupData['questions'] ?? [] as $questionData) {
                $keptQuestions[] = $this->saveQuestion($survey, $questionData, $group->id)->id;
            }
        }

        foreach ($validated['questions'] as $questionData) {
            $keptQuestions[] = $this->saveQuestion($survey, $questionData, null)->id;
        }

        $removed = $survey->questions()->whereKeyNot($keptQuestions)->get();
        $answered = $removed->first(fn (SurveyQuestion $question): bool => $question->answers()->exists());

        if ($answered !== null) {
            throw AnsweredQuestionException::removed($answered->question);
        }

        SurveyQuestion::query()->whereKey($removed->modelKeys())->delete();
        $survey->groups()->whereKeyNot($keptGroups)->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveGroup(Survey $survey, array $data): SurveyGroup
    {
        $group = isset($data['id']) ? $survey->groups()->findOrFail((int) $data['id']) : $survey->groups()->make();
        $group->fill(['title' => $data['title']])->save();

        return $group;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveQuestion(Survey $survey, array $data, ?int $groupId): SurveyQuestion
    {
        $question = isset($data['id']) ? $survey->questions()->findOrFail((int) $data['id']) : $survey->questions()->make();

        $type = QuestionType::from($data['type']);

        $question->fill([
            'question' => $data['question'],
            'type' => $type,
            'options' => $type === QuestionType::Choice ? array_values($data['options'] ?? []) : null,
            'group_id' => $groupId,
        ])->save();

        return $question;
    }
}
