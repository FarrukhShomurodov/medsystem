<?php

namespace App\Exceptions\Stock;

/**
 * A document (transfer, supply request, prescription) asked to move to a status its current
 * status does not lead to: a repeated request, or one made from a stale page.
 */
class InvalidStatusTransitionException extends StockException
{
    public function __construct(string $document, string $from, string $to)
    {
        parent::__construct("{$document} в статусе «{$from}» нельзя перевести в статус «{$to}».");
    }
}
