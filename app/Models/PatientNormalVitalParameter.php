<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientNormalParameter extends Model
{
    protected $fillable = [
        'normal_parameter_id',
        'patient_id',
        'normal',
    ];
}
