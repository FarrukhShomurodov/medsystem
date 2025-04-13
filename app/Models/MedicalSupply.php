<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MedicalSupply extends Model
{
    protected $fillable = [
        'name'
    ];

    public function warehouse(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_medical_supplies', 'supply_id', 'warehouse_id')->withPivot('quantity');
    }
}
