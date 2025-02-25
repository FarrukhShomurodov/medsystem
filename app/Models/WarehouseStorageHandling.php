<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseStorageHandling extends Model
{
    protected $fillable = [
        'warehouse_id',

        // Условия хранения
        'temperature',
        'humidity',
        'light_protected',

        // Особенности хранения
        'is_dangerous',
        'refrigeration',
        'freezing',
        'flammable',

        // Местоположение товаров
        'shelf_location',
        'special_zone',
        'label',
    ];
}
