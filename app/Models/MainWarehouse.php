<?php

namespace App\Models;

use Database\Factories\MainWarehouseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The hospital's main warehouse ("Общий склад"). There is exactly one, seeded with a fixed id.
 */
class MainWarehouse extends Model
{
    /** @use HasFactory<MainWarehouseFactory> */
    use HasFactory;

    /** Seeded by WarehouseSeeder; stock code and seeders rely on this id. */
    public const MAIN = 1;

    protected $fillable = [
        'name',
    ];

    public static function main(): self
    {
        return static::query()->findOrFail(self::MAIN);
    }

    /** @return HasMany<MainWarehouseMedication, $this> */
    public function medicationBatches(): HasMany
    {
        return $this->hasMany(MainWarehouseMedication::class);
    }

    /** @return HasMany<MainWarehouseMedicalSupply, $this> */
    public function supplyStock(): HasMany
    {
        return $this->hasMany(MainWarehouseMedicalSupply::class);
    }

    /** @return HasMany<MainWarehouseHouseholdMaterial, $this> */
    public function householdStock(): HasMany
    {
        return $this->hasMany(MainWarehouseHouseholdMaterial::class);
    }

    /**
     * @return BelongsToMany<Medication, $this, MainWarehouseMedication>
     */
    public function medication(): BelongsToMany
    {
        return $this->belongsToMany(
            Medication::class,
            'main_warehouse_medications',
            'main_warehouse_id',
            'medication_id',
        )
            ->using(MainWarehouseMedication::class)
            ->withPivot([
                'id',
                'quantity',
                'expiration_date',
                'date_of_receipt',
                'batch_number',
                'retail_price',
                'wholesale_price',
                'supplier_id',
                'packaging_type',
                'quantity_per_package',
                'package_size',
                'packaging_date',
                'markup',
                'total_price',
                'incoming_price_total',
                'incoming_price_per_unit',
                'incoming_sum',
            ]);
    }
}
