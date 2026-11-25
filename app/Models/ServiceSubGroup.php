<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceSubGroup extends Model
{
    protected $fillable = ['name', 'service_group_id'];

    /**
     * @return BelongsTo<ServiceGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ServiceGroup::class, 'service_group_id');
    }
}
