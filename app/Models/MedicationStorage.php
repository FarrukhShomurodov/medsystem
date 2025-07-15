<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationStorage extends Model
{
    protected $fillable = [
        'medication_id',
        'storage_temperature',
        'storage_conditions',
    ];
}
