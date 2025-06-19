<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Staff extends Model
{

    protected $fillable = [
        'name',
        'last_name',
        'middle_name',
        'date_birthday',
        'gender',
        'citizenship',
        'avatar_url',
        'sign_url',
    ];

    const GENDER = ['male', 'female'];

    public function contact(): HasOne
    {
        return $this->hasOne(StaffContact::class);
    }

    public function document(): HasOne
    {
        return $this->hasOne(StaffDocument::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'staff_service_assignment', 'staff_id', 'staff_service_id');
    }

    public function surveys(): BelongsToMany
    {
        return $this->belongsToMany(Survey::class, 'staff_surveys', 'staff_id', 'survey_id');
    }

    public function professionalData(): HasOne
    {
        return $this->hasOne(StaffProfessionalData::class);
    }

    public function schedule(): hasMany
    {
        return $this->hasMany(StaffSchedule::class);
    }

    public function absence(): HasMany
    {
        return $this->hasMany(StaffAbsence::class);
    }

    public function receptions(): HasMany
    {
        return $this->hasMany(Reception::class);
    }

    public function admin(): hasOne
    {
        return $this->hasOne(Admin::class);
    }

    public function isWorkingToday(Carbon $date): bool
    {
        if (!$this->document()->where('is_work', 1)->count() > 0) {
            return false;
        }

        $dayMap = [
            'Monday' => 'Понедельник',
            'Tuesday' => 'Вторник',
            'Wednesday' => 'Среда',
            'Thursday' => 'Четверг',
            'Friday' => 'Пятница',
            'Saturday' => 'Суббота',
            'Sunday' => 'Воскресенье',
        ];

        $date = Carbon::parse($date ?? now(), 'Asia/Tashkent');
        $englishDay = $date->format('l');
        $russianDay = $dayMap[$englishDay];

        $dayOfWeekId = DayOfWeek::query()->where('name', $russianDay)->value('id');

        $isScheduled = $this->schedule()->where('day_of_week_id', $dayOfWeekId)->exists();

        $isAbsent = $this->absence()->where(function ($query) use ($date) {
            $query->where(function ($q1) use ($date) {
                $q1->where('type', 'day')->whereDate('start_date', '=', $date);
            })
                ->orWhere(function ($q2) use ($date) {
                    $q2->where('type', 'period')
                        ->whereDate('start_date', '<=', $date)
                        ->whereDate('end_date', '>=', $date);
                });
        })->exists();

        return $isScheduled && !$isAbsent;
    }


    public function getTodayAvailability($date = null)
    {
        if (!$this->document()->where('is_work', 1)->exists()) {
            return [];
        }

        if (!$this->services()->where('is_appointment', true)->exists()) {
            return [];
        }


        $dayMap = [
            'Monday' => 'Понедельник',
            'Tuesday' => 'Вторник',
            'Wednesday' => 'Среда',
            'Thursday' => 'Четверг',
            'Friday' => 'Пятница',
            'Saturday' => 'Суббота',
            'Sunday' => 'Воскресенье',
        ];

        $date = Carbon::parse($date ?? now(), 'Asia/Tashkent');

        $englishDay = $date->format('l');
        $russianDay = $dayMap[$englishDay];

        $dayOfWeekId = DayOfWeek::query()->where('name', $russianDay)->value('id');

        // Получаем все расписания на этот день
        $schedules = $this->schedule()
            ->where('day_of_week_id', $dayOfWeekId)
            ->where('type', 'reception')
            ->get();

        if ($schedules->isEmpty()) {
            return [];
        }

        // Проверка на отсутствие (отпуск и т.д.)
        $isAbsent = $this->absence()
            ->where(function ($query) use ($date) {
                $query->where(function ($q1) use ($date) {
                    $q1->where('type', 'day')->whereDate('start_date', '=', $date);
                })
                    ->orWhere(function ($q2) use ($date) {
                        $q2->where('type', 'period')
                            ->whereDate('start_date', '<=', $date)
                            ->whereDate('end_date', '>=', $date);
                    });
            })->exists();

        if ($isAbsent) {
            return [];
        }

        $slots = [];
        $intervalMinutes = $this->professionalData->admission_period;
        $time = Carbon::createFromTime(0, 0);
        $endOfDay = Carbon::createFromTime(23, 59);

        while ($time <= $endOfDay) {
            $isAvailable = false;

            foreach ($schedules as $schedule) {
                $startTime = Carbon::parse($schedule->start_time);
                $endTime = Carbon::parse($schedule->end_time);

                if ($time->betweenIncluded($startTime, $endTime)) {
                    $isAvailable = true;
                    break;
                }
            }

            $slots[] = [
                'time' => $time->format('H:i'),
                'is_available' => $isAvailable,
                'admission_period' => $intervalMinutes,
            ];

            $time->addMinutes($intervalMinutes);
        }

        return $slots;
    }

    public function getBookedTime($date = null)
    {
        if (!$this->document()->where('is_work', 1)->exists()) {
            return [];
        }

        if (!$this->services()->where('is_appointment', true)->exists()) {
            return [];
        }

        $dayMap = [
            'Monday' => 'Понедельник',
            'Tuesday' => 'Вторник',
            'Wednesday' => 'Среда',
            'Thursday' => 'Четверг',
            'Friday' => 'Пятница',
            'Saturday' => 'Суббота',
            'Sunday' => 'Воскресенье',
        ];

        $date = Carbon::parse($date ?? now(), 'Asia/Tashkent');

        $englishDay = $date->format('l');
        $russianDay = $dayMap[$englishDay];

        $dayOfWeekId = DayOfWeek::query()->where('name', $russianDay)->value('id');

        $receptions = $this->receptions()
            ->where('status', '!=', 'cancelled')
            ->whereDate('date', '=', $date->format('Y-m-d'))
            ->get();

        if ($receptions->isEmpty()) {
            return [];
        }

        $slots = [];
        $intervalMinutes = $this->professionalData->admission_period;
        $time = Carbon::createFromTime(0, 0);
        $endOfDay = Carbon::createFromTime(23, 59);

        while ($time <= $endOfDay) {
            $reception = $receptions->first(function ($reception) use ($time, $intervalMinutes) {
                return Carbon::parse($reception->start_time)->format('H:i') === $time->format('H:i');
            });

            $isBooked = $reception !== null;

            $slots[] = [
                'time' => $time->format('H:i'),
                'admission_period' => $intervalMinutes,
                'is_booked' => $isBooked,
                'reception_id' => $isBooked ? $reception->id : null,
            ];

            $time->addMinutes($intervalMinutes);
        }

        return $slots;
    }
}
