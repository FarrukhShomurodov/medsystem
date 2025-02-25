<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseInventory extends Model
{
    protected $fillable = [
        'warehouse_id',
        'date_of_last_inventory',
        'result'
    ];
}
