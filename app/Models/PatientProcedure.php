<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PatientProcedure extends Pivot
{
    protected $table = 'patient_procedures';

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class,'patient_id');
    }
}
