<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MainWarehouse extends Model
{
    protected $fillable = [
        'name',
    ];

    public function medicalSupplies(): BelongsToMany
    {
        return $this->belongsToMany(
            MedicalSupply::class,
            'main_warehouse_medical_supplies',
            'main_warehouse_id',
            'supply_id'

        )->withPivot('quantity');
    }

    public function householdMaterial(): BelongsToMany
    {
        return $this->belongsToMany(
            HouseholdMaterial::class,
            'main_warehouse_household_materials',
            'main_warehouse_id',
            'household_material_id'
        )->withPivot('quantity');
    }

    public function medication(): BelongsToMany
    {
        return $this->belongsToMany(
            Medication::class,
            'main_warehouse_medications',
            'main_warehouse_id',
            'medication_id'
        )
            ->using(MainWarehouseMedication::class)
            ->withPivot([
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
                'stock',
            ]);
    }
}
