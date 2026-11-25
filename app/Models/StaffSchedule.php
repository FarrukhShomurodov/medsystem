<?php

namespace App\Models;

use App\Enums\ScheduleType;
use Database\Factories\StaffScheduleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ScheduleType $type
 */
class StaffSchedule extends Model
{
    /** @use HasFactory<StaffScheduleFactory> */
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'day_of_week_id',
        'type',
        'start_time',
        'end_time',
    ];

    protected function casts(): array
    {
        return [
            'type' => ScheduleType::class,
        ];
    }

    /**
     * @return BelongsTo<DayOfWeek, $this>
     */
    public function dayOfWeek(): BelongsTo
    {
        return $this->belongsTo(DayOfWeek::class);
    }

    /**
     * @return BelongsTo<Staff, $this>
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
