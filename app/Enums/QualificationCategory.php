<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasLabel;

/**
 * The qualification category of a medical employee (staff_professional_data.category).
 */
enum QualificationCategory: string implements HasLabel
{
    use HasValues;

    case First = 'first';
    case Second = 'second';

    public function label(): string
    {
        return match ($this) {
            self::First => 'Первая',
            self::Second => 'Вторая',
        };
    }
}
