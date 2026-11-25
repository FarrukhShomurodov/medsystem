<?php

namespace App\Models;

use Database\Factories\MedicalSupplyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MedicalSupply extends Model
{
    /** @use HasFactory<MedicalSupplyFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * @return BelongsToMany<MainWarehouse, $this, Pivot>
     */
    public function mainWarehouses(): BelongsToMany
    {
        return $this->belongsToMany(MainWarehouse::class, 'main_warehouse_medical_supplies', 'supply_id', 'main_warehouse_id');
    }
}
