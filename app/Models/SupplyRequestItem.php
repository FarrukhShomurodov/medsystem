<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SupplyRequestItem extends Model
{
    protected $fillable = [
        'request_id',
        'supplyable_id',
        'supplyable_type',
        'requested_quantity',
        'approved_quantity',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(SupplyRequest::class, 'request_id');
    }

    public function supplyable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getTypeAttribute(): string
    {
        return match ($this->supplyable_type) {
            \App\Models\Medication::class => 'medication',
            \App\Models\MedicalSupply::class => 'medical_supply',
            \App\Models\HouseholdMaterial::class => 'household_material',
            default => 'unknown',
        };
    }
}
