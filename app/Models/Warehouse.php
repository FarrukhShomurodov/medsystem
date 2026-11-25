<?php

namespace App\Models;

use Database\Factories\WarehouseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A department warehouse: it orders stock from the main warehouse, dispenses medications to the
 * wards of its departments and transfers medications to pharmacies.
 */
class Warehouse extends Model
{
    /** @use HasFactory<WarehouseFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'warehouse_type_id',
        'address',
    ];

    /*
     * The detail sections are filled in on the warehouse form, but warehouses created elsewhere
     * (seeders, imports) have none: the default models keep the warehouse page readable.
     */
    /**
     * @return HasOne<WarehouseContact, $this>
     */
    public function contact(): HasOne
    {
        return $this->hasOne(WarehouseContact::class)->withDefault();
    }

    /**
     * @return HasOne<WarehouseCapacity, $this>
     */
    public function capacity(): HasOne
    {
        return $this->hasOne(WarehouseCapacity::class)->withDefault();
    }

    /**
     * @return HasOne<WarehouseStorageHandling, $this>
     */
    public function storageHandling(): HasOne
    {
        return $this->hasOne(WarehouseStorageHandling::class)->withDefault();
    }

    /**
     * The medication batches of the warehouse.
     *
     * @return HasMany<WarehouseAccounting, $this>
     */
    public function accounting(): HasMany
    {
        return $this->hasMany(WarehouseAccounting::class);
    }

    /** @return HasMany<WarehouseMedicalSupply, $this> */
    public function supplyStock(): HasMany
    {
        return $this->hasMany(WarehouseMedicalSupply::class);
    }

    /** @return HasMany<WarehouseHouseholdMaterial, $this> */
    public function householdStock(): HasMany
    {
        return $this->hasMany(WarehouseHouseholdMaterial::class);
    }

    /**
     * @return BelongsTo<WarehouseType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(WarehouseType::class, 'warehouse_type_id', 'id');
    }

    /**
     * The catalogue model this warehouse orders and stores, given by its type.
     *
     * @return class-string<Medication|HouseholdMaterial|MedicalSupply>|null
     */
    public function stockItemClass(): ?string
    {
        return WarehouseType::STOCK_ITEMS[$this->warehouse_type_id] ?? null;
    }
}
