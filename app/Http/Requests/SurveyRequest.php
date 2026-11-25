<?php

namespace App\Http\Requests;

use App\Enums\QuestionType;
use App\Models\Survey;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A survey with its ungrouped questions and its groups of questions. On the edit form the
 * existing groups and questions carry their id, so they are updated in place and the patients'
 * answers to them stay linked.
 */
class SurveyRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'survey_type_id' => ['required', 'integer', 'exists:survey_types,id'],
            'title' => ['required', 'string', 'max:255'],
            'color' => ['required', Rule::in(Survey::COLOR)],

            'questions' => ['required', 'array'],
            ...$this->questionRules('questions.*'),

            'groups' => ['nullable', 'array'],
            'groups.*.id' => ['nullable', 'integer', $this->ofThisSurvey('survey_groups')],
            'groups.*.title' => ['required', 'string', 'max:255'],
            'groups.*.questions' => ['nullable', 'array'],
            ...$this->questionRules('groups.*.questions.*'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'survey_type_id' => 'тип',
            'title' => 'заголовок',
            'color' => 'цвет фона',
            'questions' => 'вопросы',
            'questions.*.question' => 'вопрос',
            'questions.*.type' => 'тип вопроса',
            'questions.*.options.*.text' => 'опция',
            'groups.*.title' => 'название группы',
            'groups.*.questions.*.question' => 'вопрос',
            'groups.*.questions.*.type' => 'тип вопроса',
            'groups.*.questions.*.options.*.text' => 'опция',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function questionRules(string $prefix): array
    {
        return [
            "{$prefix}.id" => ['nullable', 'integer', $this->ofThisSurvey('survey_questions')],
            "{$prefix}.question" => ['required', 'string', 'max:255'],
            "{$prefix}.type" => ['required', Rule::enum(QuestionType::class)],
            "{$prefix}.options" => ['nullable', 'array'],
            "{$prefix}.options.*.text" => ['required', 'string', 'max:255'],
            "{$prefix}.options.*.score" => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Ids may only point at groups and questions of the survey being edited; a new survey has none.
     */
    private function ofThisSurvey(string $table): mixed
    {
        $survey = $this->route('survey');

        return $survey instanceof Survey
            ? Rule::exists($table, 'id')->where('survey_id', $survey->id)
            : 'prohibited';
    }
}
