<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ExaminationMapMedication extends Pivot
{
    protected $table = 'examination_map_medications';

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    public function compatibleMedications(): BelongsToMany
    {
        return $this->belongsToMany(
            Medication::class,
            'patient_medication_compatibility',
            'examination_map_medication_id',
            'compatible_medication_id',
        )->withPivot('dosage');
    }
}
