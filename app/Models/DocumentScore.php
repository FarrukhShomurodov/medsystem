<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentScore extends Model
{
    protected $fillable = [
        'document_id',
        'from_score',
        'to_score',
    ];

    protected $casts = [
        'actions' => 'json',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function finished(): HasMany
    {
        return $this->hasMany(PatientDocumentScoreFinished::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(DocumentScoreAction::class);
    }
}

