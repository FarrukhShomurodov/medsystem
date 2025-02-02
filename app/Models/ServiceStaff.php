<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceStaff extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'service_staff_specialty_id',
    ];

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(ServiceStaffSpecialty::class,  'service_staff_specialty_id', 'id');
    }
}
