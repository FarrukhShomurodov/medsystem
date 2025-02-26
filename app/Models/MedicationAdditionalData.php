<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationAdditionalData extends Model
{
    protected $fillable = [
        'medication_id',
        'manufacturing_license_number',
        'additional_usage_conditions',
        'prescription_category',
        'specific_features',
    ];
}

