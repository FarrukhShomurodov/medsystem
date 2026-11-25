<?php

namespace App\Models;

use Database\Factories\RoomBedFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomBed extends Model
{
    /** @use HasFactory<RoomBedFactory> */
    use HasFactory;

    protected $fillable = [
        'bed_number',
        'ward_room_id',
    ];

    /** @return BelongsTo<WardRoom, $this> */
    public function wardRoom(): BelongsTo
    {
        return $this->belongsTo(WardRoom::class, 'ward_room_id');
    }
}
