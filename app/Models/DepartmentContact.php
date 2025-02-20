<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentContact extends Model
{
    protected $fillable = [
        'department_id',
        'phone',
        'address',
        'email',
        'fax',
        'head_name',
        'head_position',
        'head_contact'
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
