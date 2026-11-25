<?php

namespace App\Http\Requests\Scheduling;

use App\Enums\ReceptionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The outcome of an appointment set from the reception calendars. Confirmation and payment are
 * steps of the registrar and cash desk screens, which check their own permissions
 * (confirm_patient_registration, accept_payments), so change_receptions_status cannot set them.
 */
class ReceptionStatusRequest extends FormRequest
{
    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ReceptionStatus::class)->only([ReceptionStatus::Done, ReceptionStatus::Cancelled])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'status' => 'статус',
        ];
    }

    public function status(): ReceptionStatus
    {
        return ReceptionStatus::from($this->validated('status'));
    }
}
