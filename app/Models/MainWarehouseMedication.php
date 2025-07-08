<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MainWarehouseMedication extends Pivot
{
    protected $table = 'main_warehouse_medications';

    protected $casts = [
        'expiration_date' => 'date',
        'date_of_receipt' => 'date',
        'packaging_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
