<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PatientVitalParameter extends Pivot
{
    protected $table = 'patient_vital_parameters';

    protected $fillable = [
        'patient_id',
        'vital_parameter_id',
        'value',
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
