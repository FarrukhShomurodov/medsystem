<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationApplication extends Model
{
    protected $fillable = [
        'medication_id',
        'indications',
        'administration_method',
        'dosage_regimen',
        'contraindications',
        'usage_features',
        'side_effects',
        'drug_interactions',
    ];
}
