<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffContact extends Model
{
    protected $fillable = [
        'staff_id',
        'phone',
        'email',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
    ];
}
