<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseTransfers extends Model
{
    protected $fillable = [
        'warehouse_id',
        'supplier_id',
        'transport',
        'invoices',
        'quality_certificates',
        'acceptance_certificates',
    ];
}
