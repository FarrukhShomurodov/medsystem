<?php

namespace App\Exceptions\Billing;

use App\Services\Billing\BillableType;
use DomainException;

/**
 * The cashier's payment was not taken; nothing of it was saved. The message is shown to the cashier.
 */
final class PaymentRefusedException extends DomainException
{
    /**
     * @param  list<int>  $ids
     */
    public static function missing(BillableType $type, array $ids): self
    {
        return new self("Оплата не проведена: записи не найдены ({$type->label()} №".implode(', №', $ids).').');
    }

    public static function severalPatients(): self
    {
        return new self('Оплата не проведена: в одной оплате могут быть записи только одного пациента.');
    }

    /**
     * @param  list<string>  $orders  "Услуга №12 — Оплачено"
     */
    public static function notPayable(array $orders): self
    {
        return new self('Оплата не проведена: '.implode(', ', $orders).'. Оплатить можно только записи, подтверждённые регистратурой и ещё не оплаченные.');
    }
}
