<?php

namespace App\Http\Requests;

use App\Models\ExaminationMap;
use App\Models\SurveyQuestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A filled-in survey: from the patient card (patient, optionally the service it documents) or
 * from the survey page of an examination map (the map, whose patient it is). Answers are keyed
 * by question id and must answer questions of that survey.
 */
class SurveyResponseRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $patientId = (int) $this->input('patient_id');
        $questionIds = SurveyQuestion::query()
            ->where('survey_id', (int) $this->input('survey_id'))
            ->pluck('id')
            ->all();

        return [
            'survey_id' => ['required', 'integer', 'exists:surveys,id'],
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'examination_map_id' => ['nullable', 'integer', Rule::exists('examination_maps', 'id')->where('patient_id', $patientId)],
            'patient_service_id' => ['nullable', 'integer', Rule::exists('patient_services', 'id')->where('patient_id', $patientId)],
            'answers' => ['required', 'array:'.implode(',', $questionIds)],
            'answers.*' => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array{survey_id: int, patient_id: int, examination_map_id: int|null, patient_service_id: int|null, answers: array<int|string, string>}
     */
    public function surveyResponse(): array
    {
        return [
            'survey_id' => $this->integer('survey_id'),
            'patient_id' => $this->integer('patient_id'),
            'examination_map_id' => $this->filled('examination_map_id') ? $this->integer('examination_map_id') : null,
            'patient_service_id' => $this->filled('patient_service_id') ? $this->integer('patient_service_id') : null,
            'answers' => array_map(strval(...), $this->array('answers')),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'survey_id' => 'опросник',
            'patient_id' => 'пациент',
            'examination_map_id' => 'карта обследования',
            'patient_service_id' => 'услуга',
            'answers' => 'ответы',
            'answers.*' => 'ответ',
        ];
    }

    /**
     * The survey page of an examination map sends the map only.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->filled('patient_id') && is_numeric($this->input('examination_map_id'))) {
            $this->merge([
                'patient_id' => ExaminationMap::query()->whereKey((int) $this->input('examination_map_id'))->value('patient_id'),
            ]);
        }
    }
}
