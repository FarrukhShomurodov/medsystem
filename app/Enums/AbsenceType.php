<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasLabel;

/**
 * staff_absences.type: a single day (no end date) or a period.
 */
enum AbsenceType: string implements HasLabel
{
    use HasValues;

    case Day = 'day';
    case Period = 'period';

    public function label(): string
    {
        return match ($this) {
            self::Day => 'Один день',
            self::Period => 'Период',
        };
    }
}
