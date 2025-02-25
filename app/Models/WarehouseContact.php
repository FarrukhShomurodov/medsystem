<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseContact extends Model
{
    protected $fillable = [
        'warehouse_id',
        'phone',
        'email',
        'work_time',
        'head_name',
        'head_position',
        'head_contact'
    ];
}
