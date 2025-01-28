<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laboratory extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
    ];


    public function analyzes(): HasMany
    {
        return $this->hasMany(Analyze::class);
    }
}
