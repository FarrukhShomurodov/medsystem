<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationCostAndSupply extends Model
{
    protected $fillable = [
        'medication_id',
        'retail_price',
        'wholesale_price',
        'supplier_id',
        'delivery_time',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
