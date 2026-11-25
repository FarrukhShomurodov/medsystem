<?php

namespace App\Exceptions\Stock;

class InsufficientStockException extends StockException
{
    public function __construct(string $itemName, int $requested, int $available, string $place)
    {
        parent::__construct("Недостаточно «{$itemName}» ({$place}): нужно {$requested}, в наличии {$available}.");
    }
}
