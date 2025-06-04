<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = [
        'title',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(DocumentQuestion::class);
    }

}
