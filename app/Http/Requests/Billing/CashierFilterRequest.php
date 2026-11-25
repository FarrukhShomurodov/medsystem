<?php

namespace App\Http\Requests\Billing;

use App\Queries\Billing\CashierFilter;
use App\Services\Billing\PaymentState;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The filters of the cashier list and of its Excel export.
 * Authorization is the routes': they require view_cashier.
 */
class CashierFilterRequest extends FormRequest
{
    /** Invalid filters lead back to the unfiltered list, with the errors shown. */
    protected $redirectRoute = 'cashier.index';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Bytes that are not UTF-8 and NUL bytes never get here (RejectMalformedInput); a name search
        // has no use for the other control characters either.
        $printable = 'regex:/^\P{Cc}*$/u';

        return [
            'first_name' => ['nullable', 'string', 'max:100', $printable],
            'last_name' => ['nullable', 'string', 'max:100', $printable],
            'status' => ['nullable', Rule::enum(PaymentState::class)],
            'service_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'имя пациента',
            'last_name' => 'фамилия пациента',
            'status' => 'статус оплаты',
            'service_date' => 'дата',
        ];
    }

    public function filter(): CashierFilter
    {
        return new CashierFilter(
            firstName: $this->filled('first_name') ? $this->string('first_name')->trim()->value() : null,
            lastName: $this->filled('last_name') ? $this->string('last_name')->trim()->value() : null,
            state: $this->enum('status', PaymentState::class) ?? PaymentState::Pending,
            date: $this->date('service_date', 'Y-m-d')?->toImmutable(),
        );
    }
}
