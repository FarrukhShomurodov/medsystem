<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentScore extends Model
{
    protected $fillable = [
        'document_id',
        'from_score',
        'to_score',
        'actions',
    ];

    protected $casts = [
        'actions' => 'json',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}

