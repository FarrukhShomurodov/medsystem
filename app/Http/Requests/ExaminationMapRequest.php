<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExaminationMapRequest extends FormRequest
{

    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => 'required|int',
            'doctor_id' => 'required|int',
            'institution_id' => 'required|int',
            'admission_date' => 'required|date',
            'discharge_date' => 'nullable|date',
            'admission_diagnosis_code' => 'required|string|max:255',
            'admission_diagnosis_description' => 'required|string',
            'discharge_diagnosis_code' => 'nullable|string|max:255',
            'discharge_diagnosis_description' => 'nullable|string',
            'examination_purpose' => 'nullable|string',
            'complaints' => 'required|string',
            'general_anamnesises' => 'required|string',
            'disease_history' => 'required|string',
            'created_by' => 'nullable'
        ];
    }
}
