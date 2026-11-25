<?php

namespace App\Http\Requests\Clinical;

use App\Enums\ServiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A status picked on the patient card. Payment is the cashier's: "paid" is not accepted here.
 */
class PatientServiceStatusRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ServiceStatus::class)->except([ServiceStatus::Paid])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['status' => 'статус'];
    }

    public function status(): ServiceStatus
    {
        return ServiceStatus::from($this->validated('status'));
    }
}
