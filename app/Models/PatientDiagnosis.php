<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientDiagnosis extends Model
{
    protected $fillable = [
        'patient_id',
        'examination_map_id',
        'description'
    ];
}
