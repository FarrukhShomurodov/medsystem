<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasLabel;

/**
 * Where an ordered service is carried out (patient_services.place).
 */
enum ServicePlace: string implements HasLabel
{
    use HasValues;

    case Institution = 'institution';
    case Cabinet = 'cabinet';

    public function label(): string
    {
        return match ($this) {
            self::Institution => 'Учреждение',
            self::Cabinet => 'Кабинет',
        };
    }
}
