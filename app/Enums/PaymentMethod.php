<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasLabel;

/**
 * How the patient pays for a service, as noted on the order form (patient_services.payment.method).
 */
enum PaymentMethod: string implements HasLabel
{
    use HasValues;

    case Cash = 'cash';
    case Card = 'card';
    case InsuranceCompany = 'insurance_company';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Наличные',
            self::Card => 'Карта',
            self::InsuranceCompany => 'Страховая компания',
        };
    }
}
