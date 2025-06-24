<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientParameter extends Model
{
    protected $fillable = [
        'patient_id',
        'systolic',
        'diastolic',
        'pulse',
        'temperature',
        'saturation',
        'breathing_rate',
        'consciousness',
        'oxygen_type',
        'pain_score',
        'fall_risk',
        'urine_balance',
        'total_score',
    ];
}
