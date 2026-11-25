<?php

namespace App\Services\Billing;

use InvalidArgumentException;

/**
 * A money amount kept in minor units (tiyin, cents), so that sums of prices stay exact.
 * Prices live in decimal(14,2) columns, which Eloquent hands over as strings.
 */
final readonly class Amount
{
    private const string DECIMAL = '/^(-?)(\d+)(?:\.(\d{1,2})0*)?$/';

    private function __construct(public int $minorUnits) {}

    public static function zero(): self
    {
        return new self(0);
    }

    /**
     * "150000.00", "12.5" or 7; null (a missing price) counts as zero.
     *
     * A float is what a decimal column looks like when read without its cast; every
     * decimal(14,2) value survives the trip through round() exactly.
     */
    public static function fromDecimal(string|int|float|null $value): self
    {
        if ($value === null || $value === '') {
            return self::zero();
        }

        if (is_float($value)) {
            return new self((int) round($value * 100));
        }

        if (preg_match(self::DECIMAL, trim((string) $value), $parts) !== 1) {
            throw new InvalidArgumentException("Not a money amount with at most two decimals: {$value}");
        }

        $minorUnits = (int) $parts[2] * 100 + (int) str_pad($parts[3] ?? '', 2, '0');

        return new self($parts[1] === '-' ? -$minorUnits : $minorUnits);
    }

    public function plus(self $other): self
    {
        return new self($this->minorUnits + $other->minorUnits);
    }

    /** "150000.00": the plain form for spreadsheet cells. */
    public function toDecimal(): string
    {
        return $this->sign().intdiv(abs($this->minorUnits), 100).'.'.$this->cents();
    }

    /** "150 000,00" with non-breaking spaces: the Russian form for reading on screen. */
    public function format(): string
    {
        return $this->sign().number_format(intdiv(abs($this->minorUnits), 100), 0, '', "\u{00A0}").','.$this->cents();
    }

    private function sign(): string
    {
        return $this->minorUnits < 0 ? '-' : '';
    }

    private function cents(): string
    {
        return str_pad((string) (abs($this->minorUnits) % 100), 2, '0', STR_PAD_LEFT);
    }
}
