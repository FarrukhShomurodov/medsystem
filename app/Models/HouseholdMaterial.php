<?php

namespace App\Models;

use Database\Factories\HouseholdMaterialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class HouseholdMaterial extends Model
{
    /** @use HasFactory<HouseholdMaterialFactory> */
    use HasFactory;

    protected $fillable = [
        'batch_number',
        'name',
        'entry_price_total',
        'entry_price_unit',
    ];

    protected function casts(): array
    {
        return [
            'entry_price_total' => 'decimal:2',
            'entry_price_unit' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsToMany<MainWarehouse, $this, Pivot>
     */
    public function mainWarehouses(): BelongsToMany
    {
        return $this->belongsToMany(MainWarehouse::class, 'main_warehouse_household_materials', 'household_material_id', 'main_warehouse_id');
    }
}
