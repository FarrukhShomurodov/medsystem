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
    ];

    public function warehouse(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_household_materials', 'household_material_id', 'warehouse_id')->withPivot('quantity');
    }

    public function mainWarehouses(): BelongsToMany
    {
        return $this->belongsToMany(MainWarehouse::class, 'main_warehouse_household_materials', 'household_material_id', 'main_warehouse_id');
    }
}
