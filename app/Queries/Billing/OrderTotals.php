<?php

namespace App\Queries\Billing;

use App\Services\Billing\CurrencyTotals;

/**
 * How many orders a cashier list holds and what they come to (CashierItemsQuery::totals()).
 */
final readonly class OrderTotals
{
    public function __construct(
        public int $orders,
        public CurrencyTotals $sums,
    ) {}
}
