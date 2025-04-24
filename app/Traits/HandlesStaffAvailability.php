<?php

namespace App\Traits;

use App\Models\DayOfWeek;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

trait HandlesStaffAvailability
{
    public function scopeAvailableStaffOnDate($positionId = null): Collection
    {
        $dayMap = [
            'Monday' => 'Понедельник',
            'Tuesday' => 'Вторник',
            'Wednesday' => 'Среда',
            'Thursday' => 'Четверг',
            'Friday' => 'Пятница',
            'Saturday' => 'Суббота',
            'Sunday' => 'Воскресенье',
        ];

        $date = Carbon::parse($date ?? now());
        $englishDay = $date->format('l');
        $russianDay = $dayMap[$englishDay];

        $dayOfWeekId = DayOfWeek::query()->where('name', $russianDay)->value('id');

        $query = Staff::query();

        if ($positionId) {
            $query->whereHas('professionalData', function ($q) use ($positionId) {
                $q->where('position_id', $positionId);
            });
        }

        return $query->whereHas('schedule', function ($query) use ($dayOfWeekId) {
            $query->where('day_of_week_id', $dayOfWeekId);
        })
            ->whereDoesntHave('absence', function ($query) use ($date) {
                $query->where(function ($q) use ($date) {
                    $q->where(function ($q1) use ($date) {
                        $q1->where('type', 'day')
                            ->whereDate('start_date', '=', $date);
                    })
                        ->orWhere(function ($q2) use ($date) {
                            $q2->where('type', 'period')
                                ->whereDate('start_date', '<=', $date)
                                ->whereDate('end_date', '>=', $date);
                        });
                });
            })
            ->with([
                'schedule' => function ($query) use ($dayOfWeekId) {
                    $query->where('day_of_week_id', $dayOfWeekId)->with('dayOfWeek');
                },
                'absence' => function ($query) use ($date) {
                    $query->where(function ($q) use ($date) {
                        $q->where(function ($q1) use ($date) {
                            $q1->where('type', 'day')
                                ->whereDate('start_date', '=', $date);
                        })
                            ->orWhere(function ($q2) use ($date) {
                                $q2->where('type', 'period')
                                    ->whereDate('start_date', '<=', $date)
                                    ->whereDate('end_date', '>=', $date);
                            });
                    });
                }
            ])
            ->get();
    }
}
