<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $fillable = [
        'name',
        'equipment_type_id',
        'manufacturer_id',
        'serial_number',
        'date_purchase',
        'calibration_date',
        'repair',
        'term_date',
        'term_notify_days',
        'upcoming_service_at',
        'location',
        'staff_id',
        'price',
        'last_inspection',
        'status',
    ];

    const STATUS = ['работает', 'сломан'];
}
