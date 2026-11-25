<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExaminationMapRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', Rule::exists('patients', 'id')],
            'staff_id' => ['required', 'integer', Rule::exists('staff', 'id')],
            // A stay belongs to an existing department: the select offers no free-text entry.
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'admission_date' => 'required|date',
            // A discharge date closes the stay for good (a closed map is read-only): a planned date
            // would take the patient off the ward lists before they leave.
            'discharge_date' => 'nullable|date|after_or_equal:admission_date|before_or_equal:today',
            'admission_diagnosis_code' => 'nullable|string|max:255',
            'admission_diagnosis_description' => 'nullable|string',
            'discharge_diagnosis_code' => 'nullable|string|max:255',
            'discharge_diagnosis_description' => 'nullable|string',
            'examination_purpose' => 'nullable|string',
            'complaints' => 'nullable|string',
            'general_anamnesises' => 'nullable|string',
            'disease_history' => 'nullable|string',
            'is_outpatient' => 'sometimes|boolean',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'patient_id' => 'Пациент',
            'staff_id' => 'Лечащий врач',
            'department_id' => 'Отделение',
            'admission_date' => 'Дата поступления',
            'discharge_date' => 'Дата выписки',
        ];
    }
}
