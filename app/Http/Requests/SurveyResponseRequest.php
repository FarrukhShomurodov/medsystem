<?php

namespace App\Http\Requests;

use App\Models\PatientService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SurveyResponseRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        $rules = [
            'survey_id' => 'required|int|exists:surveys,id',
            'examination_map_id' => 'required|int|exists:examination_maps,id',
            'answers' => 'required|array',
        ];

        $rules = array_merge($rules, [
            'service_id' => 'nullable|exists:services,id',
            'service_date' => 'nullable|date',
            'place' => ['nullable', Rule::in(PatientService::PLACE)],
            'service_status' => ['nullable', Rule::in(PatientService::STATUS)],
            'staffs' => 'nullable|array',
            'staffs.*' => 'required|int|exists:staff,id',

            'procedures' => 'nullable|array',
            'procedures.*' => 'nullable|string',
            'procedure_dates' => [
                'nullable',
                'array',
                Rule::when($this->has('procedures'), [
                    'size:' . count($this->input('procedures', []))
                ])
            ],
            'procedure_dates.*' => 'date',

            'medications' => 'nullable|array',
            'medications.*' => 'nullable|string',
            'dosages' => [
                'nullable',
                'array',
                Rule::when($this->has('procedures'), [
                    'size:' . count($this->input('medications', []))
                ])
            ],
            'dosages.*' => 'nullable|string|max:50',
            'admission_periods' => [
                'nullable',
                'array',
                Rule::when($this->has('procedures'), [
                    'size:' . count($this->input('medications', []))
                ])
            ],
            'admission_periods.*' => 'nullable|string|max:100',
            'medication_notes' => 'nullable|array',
            'medication_notes.*' => 'nullable|string|max:500',
        ]);

        return $rules;
    }
}
