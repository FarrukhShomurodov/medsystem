<?php

namespace App\Models;

use Database\Factories\FloorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Floor extends Model
{
    /** @use HasFactory<FloorFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * @return HasMany<Department, $this>
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * @return HasMany<Office, $this>
     */
    public function offices(): HasMany
    {
        return $this->hasMany(Office::class);
    }
}
