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
        'discount',
        'currency',
        'service_type_id',
        'service_group_id',
        'is_active',
        'is_appointment',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ServiceGroup::class, 'service_group_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class, 'service_category_id');
    }
}
