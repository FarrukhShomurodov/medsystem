<?php

namespace App\Models;

use Database\Factories\WarehouseTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * What a department warehouse stores. The three seeded types have fixed ids (WarehouseSeeder):
 * a warehouse of a type orders and holds only the matching catalogue items.
 */
class WarehouseType extends Model
{
    /** @use HasFactory<WarehouseTypeFactory> */
    use HasFactory;

    public const MEDICATIONS = 1;

    public const HOUSEHOLD_MATERIALS = 2;

    public const MEDICAL_SUPPLIES = 3;

    /** type id => the catalogue model the warehouse stores */
    public const STOCK_ITEMS = [
        self::MEDICATIONS => Medication::class,
        self::HOUSEHOLD_MATERIALS => HouseholdMaterial::class,
        self::MEDICAL_SUPPLIES => MedicalSupply::class,
    ];

    protected $fillable = [
        'name',
    ];
}
