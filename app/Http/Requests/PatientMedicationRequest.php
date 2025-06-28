<?php

namespace App\Http\Requests;

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
            'admission_periods' => [
                'required',
                'array',
                Rule::when($this->has('procedures'), [
                    'size:' . count($this->input('medications', []))
                ])
            ],
            'admission_periods.*' => 'required|string|max:100',
            'medication_notes' => 'nullable|array',
            'medication_notes.*' => 'nullable|string|max:500',

            'compatible_medications' => 'nullable',
            'compatible_dosages' => 'nullable',
        ];
    }
}
