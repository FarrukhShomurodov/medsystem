<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationPackaging extends Model
{
    protected $fillable = [
        'medication_id',
        'packaging_type',
        'quantity_per_package',
        'package_size',
        'batch_number',
        'packaging_date',
    ];

    const TYPE = ['blister', 'bottle', 'tube'];
}
