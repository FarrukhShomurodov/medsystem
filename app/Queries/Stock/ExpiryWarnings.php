<?php

namespace App\Queries\Stock;

/**
 * Medication batches whose expiry warning is due (StockAlertsQuery).
 */
final readonly class ExpiryWarnings
{
    /**
     * @param  int  $due  batches with units left, near or past their expiry date
     * @param  int  $expired  those of them already past it, waiting to be written off
     */
    public function __construct(
        public int $due,
        public int $expired,
    ) {}
}
