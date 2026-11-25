<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PatientNormalVitalParameter extends Pivot
{
    protected $table = 'patient_normal_vital_parameters';

    /** The pivot table has its own surrogate key. */
    public $incrementing = true;

    protected $fillable = [
        'vital_parameter_id',
        'patient_id',
        'normal',
    ];

    protected function casts(): array
    {
        return [
            'normal' => 'decimal:1',
        ];
    }

    /** @return BelongsTo<VitalParameter, $this> */
    public function vitalParameter(): BelongsTo
    {
        return $this->belongsTo(VitalParameter::class);
    }

    /** @return BelongsTo<Patient, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
