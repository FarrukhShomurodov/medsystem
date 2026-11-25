<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * A diagnosis from the ICD catalogue made for a patient. The examination map is not taken from
 * the request: the diagnosis joins the patient's active map, if any.
 */
class PatientDiagnosisRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'diagnosis_id' => ['required', 'integer', 'exists:diagnoses,id'],
        ];
    }

    /**
     * @return array{patient_id: int, diagnosis_id: int}
     */
    public function diagnosis(): array
    {
        return [
            'patient_id' => $this->integer('patient_id'),
            'diagnosis_id' => $this->integer('diagnosis_id'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'patient_id' => 'пациент',
            'diagnosis_id' => 'диагноз',
        ];
    }
}
