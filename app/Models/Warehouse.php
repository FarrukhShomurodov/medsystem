<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function transfer(): HasOne
    {
        return $this->hasOne(WarehouseTransfers::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(WarehouseType::class, 'warehouse_type_id', 'id');
    }
}
