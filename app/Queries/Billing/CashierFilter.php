<?php

namespace App\Queries\Billing;

use App\Services\Billing\PaymentState;
use Carbon\CarbonImmutable;

/**
 * The filters of the cashier list. The list shows the orders waiting for payment
 * unless asked for the paid ones.
 */
final readonly class CashierFilter
{
    public function __construct(
        public ?string $firstName = null,
        public ?string $lastName = null,
        public PaymentState $state = PaymentState::Pending,
        public ?CarbonImmutable $date = null,
    ) {}
}
