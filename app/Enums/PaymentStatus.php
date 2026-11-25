<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasLabel;

/**
 * The payment as noted on the service order form (patient_services.payment.status), for instance
 * a part covered by insurance. It is a note: the cashier's payment is ServiceStatus::Paid.
 */
enum PaymentStatus: string implements HasLabel
{
    use HasValues;

    case Paid = 'paid';
    case Unpaid = 'unpaid';
    case PartiallyPaid = 'partially_paid';

    public function label(): string
    {
        return match ($this) {
            self::Paid => 'Оплачено',
            self::Unpaid => 'Не оплачено',
            self::PartiallyPaid => 'Частично оплачено',
        };
    }
}
