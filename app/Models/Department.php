<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = [
        'name',
        'warehouse_id',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(WardRoom::class, 'department_rooms');
    }

    public function staffs(): HasMany
    {
        return $this->hasMany(StaffProfessionalData::class);
    }

    public function examinationMaps(): HasMany
    {
        return $this->hasMany(ExaminationMap::class)->where('is_active', true);
    }
}
