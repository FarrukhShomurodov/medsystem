<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HouseholdMaterial extends Model
{
    protected $fillable = [
        'batch_number',
        'name',
        'entry_price_total',
        'entry_price_unit',
        'entry_amount',
    ];

    public function warehouse(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_household_materials', 'household_material_id', 'warehouse_id')->withPivot('quantity');
    }
}
