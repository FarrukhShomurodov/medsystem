<?php

namespace App\Models;

use Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Supplier extends Model
{
    /** @use HasFactory<SupplierFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'contract_terms',
        'phone',
        'email',
        'address',
        'delivery_schedule',
    ];

    /**
     * @return BelongsToMany<Manufacturer, $this, Pivot>
     */
    public function manufacturers(): BelongsToMany
    {
        return $this->belongsToMany(Manufacturer::class, 'supplier_manufacturers', 'supplier_id', 'manufacturer_id');
    }
}
