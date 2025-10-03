<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Office extends Model
{
    protected $fillable = ['name', 'floor_id'];

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }
}
