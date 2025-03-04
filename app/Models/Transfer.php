<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    protected $fillable = [
        'from_warehouse_id',
        'to_pharmacy_id',
        'transfer_date',
        'status',
        'notes'
    ];

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toPharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'to_pharmacy_id');
    }

    public function medications(): HasMany
    {
        return $this->hasMany(TransferMedication::class);
    }
}
