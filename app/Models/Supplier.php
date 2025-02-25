<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'contract_terms',
        'phone',
        'email',
        'address',
        'delivery_schedule',
    ];
}
