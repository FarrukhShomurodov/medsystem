<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class StaffAbsence extends Model
{
    const TYPE = ['day', 'period'];

    protected $fillable = [
        'staff_id',
        'type',
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
