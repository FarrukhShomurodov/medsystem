<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
