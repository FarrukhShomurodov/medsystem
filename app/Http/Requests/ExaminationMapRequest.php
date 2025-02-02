<?php

namespace App\Http\Requests;

use App\Models\Analyze;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExaminationMapRequest extends FormRequest
{

    /**
     * @return array<string>
     */
    public function rules(): array
    {
        $rules = [
            'patient_id' => 'required|int',
            'doctor_id' => 'required|int',
            'institution_id' => 'required|int',
            'admission_date' => 'required|date',
            'discharge_date' => 'nullable|date',
            'admission_diagnosis_code' => 'nullable|string|max:255',
            'admission_diagnosis_description' => 'nullable|string',
            'discharge_diagnosis_code' => 'nullable|string|max:255',
            'discharge_diagnosis_description' => 'nullable|string',
            'examination_purpose' => 'nullable|string',
            'complaints' => 'nullable|string',
            'general_anamnesises' => 'nullable|string',
            'disease_history' => 'nullable|string',


        ];


        if ($this->method() == 'PUT') {
            $rules = array_merge($rules, [
                'laboratory_id' => 'nullable',
                'types' => 'nullable|array',
                'methods' => 'nullable|array',
                'analysis_time' => 'nullable',
                'medical_staff' => ['nullable', Rule::in(Analyze::MEDICAL_STAFF)],
                'equipments' => 'nullable|array',
            ]);
        } else if ($this->method() == 'POST') {
            $rules = array_merge($rules, [
                'laboratory_id' => 'required',
                'types' => 'required|array',
                'methods' => 'required|array',
                'analysis_time' => 'required',
                'medical_staff' => ['nullable', Rule::in(Analyze::MEDICAL_STAFF)],
                'equipments' => 'required|array',
            ]);
        }

        return $rules;
    }
}
