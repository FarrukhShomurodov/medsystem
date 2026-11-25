<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasLabel;

/**
 * Why an employee is absent (staff_absences.reason).
 */
enum AbsenceReason: string implements HasLabel
{
    use HasValues;

    case Vacation = 'vacation';
    case Sick = 'sick';
    case DayOff = 'day_off';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Vacation => 'Отпуск',
            self::Sick => 'Больничный',
            self::DayOff => 'Выходной',
            self::Other => 'Другое',
        };
    }
}
