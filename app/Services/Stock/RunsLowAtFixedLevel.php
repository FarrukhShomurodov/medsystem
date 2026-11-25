<?php

namespace App\Services\Stock;

/**
 * The low-stock rule of a stock row whose item has no threshold of its own (a medical supply, a
 * household material): it runs low at or below StockLot::LOW_STOCK units.
 */
trait RunsLowAtFixedLevel
{
    public function isLowStock(): bool
    {
        return (int) $this->getAttribute('quantity') <= StockLot::LOW_STOCK;
    }
}
