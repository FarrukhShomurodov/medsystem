<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentResponse extends Model
{
    protected $fillable = [
        'patient_id',
        'examination_map_id',
        'document_id',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function map(): BelongsTo
    {
        return $this->belongsTo(ExaminationMap::class, 'examination_map_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(DocumentAnswer::class, 'document_response_id', 'id');
    }
}
