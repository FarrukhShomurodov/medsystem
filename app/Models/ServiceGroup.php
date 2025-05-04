<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceGroup extends Model
{
    protected $fillable = [
        'name'
    ];

    public function subGroups(): HasMany
    {
        return $this->hasMany(ServiceSubGroup::class);
    }
}
