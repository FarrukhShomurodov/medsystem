<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function manufacturers(): BelongsToMany
    {
        return $this->belongsToMany(Manufacturer::class, 'supplier_manufacturers', 'supplier_id', 'manufacturer_id');
    }
}
