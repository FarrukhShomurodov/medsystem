<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Diagnosis extends Model
{
    protected $fillable = [
        'code',
        'name'
    ];

    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }
}
