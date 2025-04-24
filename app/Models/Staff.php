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

    public function isWorkingToday(Carbon $date): bool
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

        $date = \Illuminate\Support\Carbon::parse($date ?? now());
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
}
