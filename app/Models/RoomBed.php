<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomBed extends Model
{
    protected $fillable = [
        'bed_number',
        'ward_room_id'
    ];

    public function wordRoom(): BelongsTo
    {
        return $this->belongsTo(WardRoom::class);
    }
}
