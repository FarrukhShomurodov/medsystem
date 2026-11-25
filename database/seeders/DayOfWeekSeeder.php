<?php

namespace Database\Seeders;

use App\Models\DayOfWeek;
use Database\Seeders\Concerns\SeedsRowsWithFixedIds;
use Illuminate\Database\Seeder;

/**
 * Staff schedules are matched to the calendar by id: DayOfWeek::idOf() maps a date to its
 * ISO-8601 weekday (Monday = 1), so the ids must keep that meaning. The names are labels only.
 */
class DayOfWeekSeeder extends Seeder
{
    use SeedsRowsWithFixedIds;

    public const DAYS = [
        1 => 'Понедельник',
        2 => 'Вторник',
        3 => 'Среда',
        4 => 'Четверг',
        5 => 'Пятница',
        6 => 'Суббота',
        7 => 'Воскресенье',
    ];

    public function run(): void
    {
        foreach (self::DAYS as $id => $name) {
            $this->firstOrCreateWithId(DayOfWeek::class, $id, ['name' => $name]);
        }

        $this->syncIdSequence('day_of_weeks');
    }
}
