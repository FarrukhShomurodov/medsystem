<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PatientNormalVitalParameter extends Pivot
{
    protected $table = 'patient_normal_vital_parameters';

    protected $fillable = [
        'vital_parameter_id',
        'patient_id',
        'normal',
    ];

    public function vitalParameter(): BelongsTo
    {
        return $this->belongsTo(VitalParameter::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
