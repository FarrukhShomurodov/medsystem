<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ServicePlace;
use App\Models\PatientService;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The details of a service ordered for a patient. The status is not part of it: a new order is
 * "assigned", and PatientServiceStatusRequest, the registrar and the cashier move it on.
 */
class PatientServiceRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // A service taken off the price list is not ordered any more; an order of it made before
            // keeps its service, so its other details can still be edited.
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where(fn (Builder $query) => $query
                ->where('is_active', true)
                ->when($this->orderedServiceId(), fn (Builder $query, int $current) => $query->orWhere('id', $current)))],
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'examination_map_id' => [
                'nullable',
                'integer',
                Rule::exists('examination_maps', 'id')->where('patient_id', (int) $this->input('patient_id')),
            ],
            'service_date' => ['required', 'date'],
            'service_time' => ['nullable', 'date_format:H:i'],
            'place' => ['nullable', Rule::enum(ServicePlace::class)],
            'result' => ['nullable', 'string'],
            'payment' => ['nullable', 'array'],
            'payment.status' => ['nullable', Rule::enum(PaymentStatus::class)],
            'payment.method' => ['nullable', Rule::enum(PaymentMethod::class)],
            'insurance_policy' => ['nullable', 'array'],
            'insurance_policy.company_name' => ['nullable', 'string', 'max:255'],
            'insurance_policy.insurance_policy_number' => ['nullable', 'string', 'max:255'],
            'insurance_policy.percentage_of_coverage' => ['nullable', 'numeric', 'between:0,100'],
        ];
    }

    /** The service of the order being edited; null on the form of a new order. */
    private function orderedServiceId(): ?int
    {
        $order = $this->route('service');

        return $order instanceof PatientService ? $order->service_id : null;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'service_id' => 'услуга',
            'patient_id' => 'пациент',
            'examination_map_id' => 'карта обследования',
            'service_date' => 'дата услуги',
            'service_time' => 'время услуги',
            'place' => 'место оказания',
        ];
    }
}
