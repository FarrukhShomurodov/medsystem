<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = [
        'name',
        'description',
        'code',
        'price',
        'price_vat',
        'discount',
        'currency',
        'service_type_id',
        'is_active',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class, 'service_category_id');
    }
}
