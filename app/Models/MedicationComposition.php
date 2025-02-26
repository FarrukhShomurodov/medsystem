<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationComposition extends Model
{
    protected $fillable = [
        'medication_id',
        'active_substance_name',
        'active_substance_dosage',
        'substance_name',
        'substance_dosage',
        'pharmacological_group',
    ];
}
