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
        return [
            'survey_id' => 'required|int|exists:surveys,id',
            'patient_id' => 'required|int|exists:patients,id',
            'patient_service_id' => 'nullable|int|exists:patient_services,id',
            'answers' => 'required|array',
        ];
    }
}
