<?php

namespace App\Http\Requests;

use App\Models\Analyze;
use App\Models\PatientService;
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
            'department_id' => 'required|int',
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


        $rules = array_merge($rules, [
            'service_id' => 'nullable|exists:services,id',
            'service_date' => 'nullable|date',
            'place' => ['nullable', Rule::in(PatientService::PLACE)],
            'service_status' => ['nullable', Rule::in(PatientService::STATUS)],
            'staffs' => 'nullable|array',
            'staffs.*' => 'required|int|exists:staff,id',

            'procedures' => 'nullable|array',
            'procedures.*' => 'required|string',
            'procedure_dates' => [
                'nullable',
                'array',
                Rule::when($this->has('procedures'), [
                    'size:' . count($this->input('procedures', []))
                ])
            ],
            'procedure_dates.*' => 'date',

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
        ]);


        return $rules;
    }
}
