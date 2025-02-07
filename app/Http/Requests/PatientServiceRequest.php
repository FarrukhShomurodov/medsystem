<?php

namespace App\Http\Requests;

use App\Models\PatientService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientServiceRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'service_id' => 'required',
            'patient_id' => 'required|int|exists:patients,id',
            'examination_map_id' => 'required|int|exists:patients,id',
            'doctor_id' => 'nullable|int|exists:doctors,id',
            'institution_id' => 'nullable|int|exists:institutions,id',
            'service_date' => 'required|date',
            'service_time' => 'nullable|date_format:H:i',
            'place' => ['required', Rule::in(PatientService::PLACE)],
            'result' => 'nullable|string',
            'status' => ['required', Rule::in(PatientService::STATUS)],
            'payment' => 'nullable|array',
            'payment.status' => ['nullable', Rule::in(PatientService::PAYMENT_STATUS)],
            'payment.method' => ['nullable', Rule::in(PatientService::PAYMENT_METHOD)],
            'insurance_policy' => 'nullable|array',
            'insurance_policy.company_name' => 'nullable|string',
            'insurance_policy.insurance_policy_number' => 'nullable|string',
            'insurance_policy.percentage_of_coverage' => 'nullable|string',

            'methods' => 'nullable|array',
            'equipments' => 'nullable|array',
            'staffs' => 'required|array',
            'staffs.*' => 'required|int|exists:service_staff,id',

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
        ];
    }
}
