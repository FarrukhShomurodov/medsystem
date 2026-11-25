<?php

namespace App\Services\Stock;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A stock row: some quantity of one catalogue item (a medication, a medical supply or a
 * household material) in one place, with its `quantity` column. Only StockLedger changes it.
 */
interface StockLot
{
    /**
     * Supplies and household materials have no threshold of their own: a row at or below this many
     * units runs low (the receipts page lists it, the remains pages mark it "мало"). A medication
     * runs low by its own threshold (Medication::isLowStock()).
     */
    public const int LOW_STOCK = 20;

    /**
     * The catalogue item the row holds.
     *
     * @return BelongsTo<covariant Model, covariant Model>
     */
    public function stockItem(): BelongsTo;
}
