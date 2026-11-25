<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationAdditionalSubstance extends Model
{
    protected $table = 'medication_additional_substance';

    protected $fillable = [
        'medication_composition_id',
        'substance_name',
        'substance_dosage',
    ];
}
