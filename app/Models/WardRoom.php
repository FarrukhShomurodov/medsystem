<?php

namespace App\Models;

use Database\Factories\WardRoomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class WardRoom extends Model
{
    /** @use HasFactory<WardRoomFactory> */
    use HasFactory;

    protected $fillable = [
        'room_number',
    ];

    /**
     * @return HasMany<RoomBed, $this>
     */
    public function beds(): HasMany
    {
        return $this->hasMany(RoomBed::class);
    }

    /**
     * @return BelongsToMany<Department, $this, Pivot>
     */
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'department_rooms');
    }
}
