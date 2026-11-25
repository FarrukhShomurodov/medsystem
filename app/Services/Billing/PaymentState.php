<?php

namespace App\Services\Billing;

use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasLabel;

/**
 * Where an order stands at the cashier, whatever status column its kind uses
 * (see BillableType::status()).
 */
enum PaymentState: string implements HasLabel
{
    use HasValues;

    case Pending = 'pending';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'К оплате',
            self::Paid => 'Оплачено',
        };
    }
}
