<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasLabel;

/**
 * The package of a medication batch
 * (main_warehouse_medications.packaging_type and medication_packagings.packaging_type).
 */
enum PackagingType: string implements HasLabel
{
    use HasValues;

    case Blister = 'blister';
    case Vial = 'vial';
    case Tube = 'tube';

    public function label(): string
    {
        return match ($this) {
            self::Blister => 'Блистер',
            self::Vial => 'Флакон',
            self::Tube => 'Тюбик',
        };
    }
}
