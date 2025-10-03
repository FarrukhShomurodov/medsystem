<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Floor extends Model
{
    protected $fillable = [
        'name'
    ];

    public function departments(): hasMany
    {
        return $this->hasMany(Department::class);
    }

    public function offices(): hasMany
    {
        return $this->hasMany(Office::class);
    }
}
