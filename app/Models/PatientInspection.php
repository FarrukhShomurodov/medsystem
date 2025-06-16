<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientInspection extends Model
{
    protected $fillable = [
        'patient_id',
        'bp',
        'pulse',
        'saturation',
        'temperature',
        'chdd',
        'consciousness_level',
        'oxygen_status',
    ];
}
