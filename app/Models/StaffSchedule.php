<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffSchedule extends Model
{
    protected $fillable = [
        'staff_id',
        'day_start',
        'day_end',
        'start_time',
        'end_time',
        'reception_start',
        'reception_end',
    ];

    const DAY_START = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    const DAY_END = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    public static function getDayOfWeekLabel(string $day): string
    {
        $days = [
            'monday' => 'Понедельник',
            'tuesday' => 'Вторник',
            'wednesday' => 'Среда',
            'thursday' => 'Четверг',
            'friday' => 'Пятница',
            'saturday' => 'Суббота',
            'sunday' => 'Воскресенье',
        ];

        return $days[$day] ?? $day;
    }
}
