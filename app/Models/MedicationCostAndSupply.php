<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationCostAndSupply extends Model
{
    protected $fillable = [
        'warehouse_accounting_id',
        'retail_price',
        'wholesale_price',
        'supplier_id',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
