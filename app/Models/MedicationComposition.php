<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

}
