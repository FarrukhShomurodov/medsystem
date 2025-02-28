<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pharmacy extends Model
{
    protected $fillable = [
        'name',
        'type_id',
        'legal_address',
        'actual_address',
        'contact_phone',
        'working_hours'
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(PharmacyType::class, 'type_id', 'id');
    }
}
