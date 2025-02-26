<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationRiskAndSafety extends Model
{
    protected $fillable = [
        'medication_id',
        'risk_assessment',
        'warnings',
        'safety_in_use',
    ];
}
