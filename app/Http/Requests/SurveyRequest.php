<?php

namespace App\Http\Requests;

use App\Models\SurveyQuestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SurveyRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'survey_type_id' => 'required',
            'title' => 'required|string|max:255',

            'questions' => 'required|array',
            'questions.*.question' => 'required|string|max:255',
            'questions.*.type' => ['required', Rule::in(SurveyQuestion::TYPE)],
            'questions.*.options' => 'nullable|array',

            'groups' => 'nullable|array',
            'groups.*.title' => 'required|string|max:255',
            'groups.*.questions' => 'nullable|array',
            'groups.*.questions.*.question' => 'required|string|max:255',
            'groups.*.questions.*.type' => ['required', Rule::in(SurveyQuestion::TYPE)],
            'groups.*.questions.*.options' => 'nullable|array',
        ];
    }
}
