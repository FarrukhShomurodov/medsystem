<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasLabel;

/**
 * A weekly interval of an employee's schedule (staff_schedules.type): time at work, or the part
 * of it in which appointments can be booked.
 */
enum ScheduleType: string implements HasLabel
{
    use HasValues;

    case Work = 'work';
    case Reception = 'reception';

    public function label(): string
    {
        return match ($this) {
            self::Work => 'Рабочие часы',
            self::Reception => 'Часы приёма',
        };
    }
}
