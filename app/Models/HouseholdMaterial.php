<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HouseholdMaterial extends Model
{
    protected $fillable = [
        'batch_number',
        'name',
        'entry_price_total',
        'entry_price_unit',
        'entry_amount',
    ];
}
