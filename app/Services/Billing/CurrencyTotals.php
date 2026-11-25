<?php

namespace App\Services\Billing;

use App\Queries\Billing\BillableItem;

/**
 * Sums of prices per currency: a price list may mix currencies, and those are never added up.
 */
final class CurrencyTotals
{
    /** @var array<string, Amount> */
    private array $totals = [];

    private function __construct() {}

    /**
     * @param  iterable<BillableItem>  $items
     */
    public static function of(iterable $items): self
    {
        $totals = new self;

        foreach ($items as $item) {
            $totals->totals[$item->currency] = ($totals->totals[$item->currency] ?? Amount::zero())->plus($item->price);
        }

        return $totals;
    }

    /**
     * Sums already added up per currency (by the database), in the order given.
     *
     * @param  array<string, Amount>  $sums  currency => sum
     */
    public static function fromSums(array $sums): self
    {
        $totals = new self;
        $totals->totals = $sums;

        return $totals;
    }

    /**
     * Currency => sum, in the order the currencies first appear;
     * nothing to pay is a zero in the default currency.
     *
     * @return non-empty-array<string, Amount>
     */
    public function all(): array
    {
        return $this->totals === [] ? [BillableItem::DEFAULT_CURRENCY => Amount::zero()] : $this->totals;
    }

    /** "150 000,00 UZS + 20,00 USD". */
    public function format(): string
    {
        return collect($this->all())
            ->map(fn (Amount $sum, string $currency): string => "{$sum->format()} {$currency}")
            ->implode(' + ');
    }
}
