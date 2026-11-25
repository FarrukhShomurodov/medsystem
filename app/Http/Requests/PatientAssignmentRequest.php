<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * A referral of a patient to a service, written by the signed-in doctor.
 */
class PatientAssignmentRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array{patient_id: int, service_id: int, comment: string|null}
     */
    public function assignment(): array
    {
        return [
            'patient_id' => $this->integer('patient_id'),
            'service_id' => $this->integer('service_id'),
            'comment' => $this->filled('comment') ? $this->string('comment')->value() : null,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'patient_id' => 'пациент',
            'service_id' => 'услуга',
            'comment' => 'комментарий',
        ];
    }
}
