<?php

namespace App\Models;

use Database\Factories\ServiceGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceGroup extends Model
{
    /** @use HasFactory<ServiceGroupFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * @return HasMany<ServiceSubGroup, $this>
     */
    public function subGroups(): HasMany
    {
        return $this->hasMany(ServiceSubGroup::class);
    }
}
