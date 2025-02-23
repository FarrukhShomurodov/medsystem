<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentEmploye extends Model
{
    protected $fillable = [
        'department_id',
        'doctors_count',
        'nurses_count',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
