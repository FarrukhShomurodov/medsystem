<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WarehouseAccounting extends Model
{
    protected $fillable = [
        'warehouse_id',
        'medication_id',
        'quantity',
        'expiration_date',
        'date_of_receipt',
        'batch_number',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    public function packaging(): HasOne
    {
        return $this->hasOne(MedicationPackaging::class);
    }

    public function medicationCost(): HasOne
    {
        return $this->hasOne(MedicationCostAndSupply::class);
    }
}
