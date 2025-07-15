<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicationComposition extends Model
{
    protected $fillable = [
        'medication_id',
        'active_substance_name',
        'active_substance_dosage',
    ];

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    public function additionalSubstance(): HasMany
    {
        return $this->hasMany(MedicationAdditionalSubstance::class);
    }
}
