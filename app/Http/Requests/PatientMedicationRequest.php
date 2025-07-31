<?php

namespace App\Http\Requests;

use App\Models\PatientMedication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientMedicationRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => 'required|int|exists:patients,id',

            'medications' => 'nullable|array',
            'medications.*' => 'required|string',
            'dosages' => [
                'required',
                'array',
                Rule::when($this->has('procedures'), [
                    'size:' . count($this->input('medications', []))
                ])
            ],
            'dosages.*' => 'required|string|max:50',

            'single_doses' => 'required|array',
            'single_doses.*' => 'required|string|max:50',

            'statuses' => 'required|array',
            'statuses.*' => ['required', Rule::in(PatientMedication::STATUS)],

            'methods' => 'required|array',
            'methods.*' => ['required', Rule::in(PatientMedication::METHOD)],

            'admission_rules' => 'required|array',
            'admission_rules.*' => ['required', Rule::in(PatientMedication::ADMISSION_RULES)],


            'admission_periods' => [
                'required',
                'array',
                Rule::when($this->has('procedures'), [
                    'size:' . count($this->input('medications', []))
                ])
            ],
            'admission_periods.*' => 'required|string|max:100',

            'medication_notes' => 'required|array',
            'medication_notes.*' => 'required|string|max:500',

            'compatible_medications' => 'nullable',
            'compatible_dosages' => 'nullable',
        ];
    }
}
