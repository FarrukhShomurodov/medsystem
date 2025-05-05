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
//        dd($this->request);

        $rules = [
            'patient_id' => 'required|int',
            'staff_id' => 'required|int',
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
            'is_outpatient' => 'sometimes|boolean',

            // analyze
//            'laboratory_id' => 'nullable',
        ];

        // analyze
//        if ($this->laboratory_id) {
//            $rules = array_merge($rules, [
//                'types' => 'required|array',
//                'methods' => 'required|array',
//                'analysis_time' => 'required',
//                'medical_staff' => ['required', Rule::in(Analyze::MEDICAL_STAFF)],
//                'equipments' => 'required|array',
//            ]);
//        } else {
//            $rules = array_merge($rules, [
//                'types' => 'nullable|array',
//                'methods' => 'nullable|array',
//                'analysis_time' => 'nullable',
//                'medical_staff' => ['nullable', Rule::in(Analyze::MEDICAL_STAFF)],
//                'equipments' => 'nullable|array',
//            ]);
//        }
//
//        // surveys
//        if ($this->survey_id) {
//            $rules = array_merge($rules, [
//                'survey_id' => 'required|int|exists:surveys,id',
//                'answers' => 'required|array',
//            ]);
//        } else {
//            $rules = array_merge($rules, [
//                'survey_id' => 'nullable|int|exists:surveys,id',
//                'answers' => 'nullable|array',
//            ]);
//        }

//        $rules = array_merge($rules, [
//            'service_id' => 'nullable|exists:services,id',
//            'service_date' => 'nullable|date',
//            'place' => ['nullable', Rule::in(PatientService::PLACE)],
//            'service_status' => ['nullable', Rule::in(PatientService::STATUS)],
//            'staffs' => 'nullable|array',
//            'staffs.*' => 'required|int|exists:staff,id',
//
//            'procedures' => 'nullable|array',
//            'procedures.*' => 'required|string',
//            'procedure_dates' => [
//                'nullable',
//                'array',
//                Rule::when($this->has('procedures'), [
//                    'size:' . count($this->input('procedures', []))
//                ])
//            ],
//            'procedure_dates.*' => 'date',
//
//            'medications' => 'nullable|array',
//            'medications.*' => 'required|string',
//            'dosages' => [
//                'nullable',
//                'array',
//                Rule::when($this->has('procedures'), [
//                    'size:' . count($this->input('medications', []))
//                ])
//            ],
//            'dosages.*' => 'nullable|string|max:50',
//            'admission_periods' => [
//                'nullable',
//                'array',
//                Rule::when($this->has('procedures'), [
//                    'size:' . count($this->input('medications', []))
//                ])
//            ],
//            'admission_periods.*' => 'nullable|string|max:100',
//            'medication_notes' => 'nullable|array',
//            'medication_notes.*' => 'nullable|string|max:500',
//
//            'compatible_medications' => 'nullable',
//            'compatible_dosages' => 'nullable',
//        ]);

        return $rules;
    }
}
