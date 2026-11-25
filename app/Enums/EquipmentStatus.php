<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasLabel;

/**
 * Whether a piece of equipment can be used (equipment.status).
 */
enum EquipmentStatus: string implements HasLabel
{
    use HasValues;

    case Working = 'working';
    case Broken = 'broken';

    public function label(): string
    {
        return match ($this) {
            self::Working => 'Работает',
            self::Broken => 'Сломан',
        };
    }
}
