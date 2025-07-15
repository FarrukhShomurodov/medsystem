<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationApplication extends Model
{
    protected $fillable = [
        'medication_id',
        'administration_method',
        'dosage_regimen',
        'drug_interactions',
    ];
}
