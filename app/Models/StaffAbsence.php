<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;


class StaffAbsence extends Model
{
    protected $fillable = [
        'staff_id',
        'start_date',
        'end_date',
        'reason',
        'description',
    ];

    const REASON = [
        'vacation',
        'sick',
        'day_off',
        'other'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];


    public static function isAbsent($staffId, $date = null): bool
    {
        $date = Carbon::parse($date ?? now());

        $absence = StaffAbsence::query()
            ->where('staff_id', $staffId)
            ->latest()
            ->first();

        if (!$absence) {
            return false;
        }

        return $absence->start_date->lte($date) && $absence->end_date->gte($date);
    }


    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public static function getReasonLabel(string $reason): string
    {
        $reasons = [
            'vacation' => 'Отпуск',
            'sick' => 'Больничный',
            'day_off' => 'Выходной',
            'other' => 'Другое',
        ];

        return $reasons[$reason] ?? $reason;
    }

}
