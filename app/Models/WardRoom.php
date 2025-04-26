<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WardRoom extends Model
{
    protected $fillable = [
        'room_number',
    ];

    public function beds(): HasMany
    {
        return $this->hasMany(RoomBed::class);
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class);
    }
}
