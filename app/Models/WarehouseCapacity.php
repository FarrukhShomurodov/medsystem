<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseCapacity extends Model
{
    protected $fillable = [
        'warehouse_id',
        'total_volume',
        'occupied_volume',
        'occupied_volume_percentage',
        'max_capacity',
    ];
}
