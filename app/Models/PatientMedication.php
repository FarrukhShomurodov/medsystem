<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PatientMedication extends Pivot
{
    protected $table = 'patient_medications';

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    public function compatibleMedications(): BelongsToMany
    {
        return $this->belongsToMany(
            Medication::class,
            'patient_medication_compatibility',
            'patient_medication_id',
            'compatible_medication_id',
        )->withPivot('dosage');
    }
}
