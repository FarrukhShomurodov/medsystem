<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Warehouse extends Model
{
    protected $fillable = [
        'name',
        'description',
        'warehouse_type_id',
        'address',
    ];

    public function contact(): HasOne
    {
        return $this->hasOne(WarehouseContact::class);
    }

    public function accounting(): HasMany
    {
        return $this->hasMany(WarehouseAccounting::class);
    }

    public function capacity(): HasOne
    {
        return $this->hasOne(WarehouseCapacity::class);
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(WarehouseInventory::class);
    }

    public function storageHandling(): HasOne
    {
        return $this->hasOne(WarehouseStorageHandling::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(WarehouseType::class, 'warehouse_type_id', 'id');
    }

    public function medicalSupplies(): BelongsToMany
    {
        return $this->belongsToMany(MedicalSupply::class, 'warehouse_medical_supplies', 'warehouse_id', 'supply_id')->withPivot('quantity');
    }
}
