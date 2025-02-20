<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentBed extends Model
{
    protected $fillable = [
        'department_id',
        'total_beds',
        'occupied_beds'
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
