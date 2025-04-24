<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffSchedule extends Model
{
    const TYPE = ['work', 'reception'];

    protected $fillable = [
        'staff_id',
        'day_of_week_id',
        'type',
        'start_time',
        'end_time',
    ];

    public function dayOfWeek(): BelongsTo
    {
        return $this->belongsTo(DayOfWeek::class);
    }
}
