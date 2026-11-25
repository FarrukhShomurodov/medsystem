<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasLabel;

/**
 * patients.gender and staff.gender.
 */
enum Gender: string implements HasLabel
{
    use HasValues;

    case Male = 'male';
    case Female = 'female';

    public function label(): string
    {
        return match ($this) {
            self::Male => 'Мужской',
            self::Female => 'Женский',
        };
    }
}
