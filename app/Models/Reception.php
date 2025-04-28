<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reception extends Model
{
    protected $fillable = [
        'examination_map_id',
        'staff_id',
        'start_time',
        'end_time',
        'date',
        'comment',
    ];

    public function map(): BelongsTo
    {
        return $this->belongsTo(ExaminationMap::class, 'examination_map_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
