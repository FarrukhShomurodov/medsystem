<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Floor extends Model
{
    protected $fillable = [
        'name'
    ];

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class);
    }

    public function offices(): BelongsToMany
    {
        return $this->belongsToMany(Office::class);
    }
}
