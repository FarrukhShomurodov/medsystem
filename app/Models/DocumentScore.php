<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A score range of a document and the actions recommended for it. An open range
 * (to_score null) runs up to the next range: the pain sheet has one row per point.
 */
class DocumentScore extends Model
{
    protected $fillable = [
        'document_id',
        'from_score',
        'to_score',
    ];

    /**
     * The range a total falls into: the highest lower bound that does not exceed it.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function containing(Builder $query, int $score): void
    {
        $query->where('from_score', '<=', $score)
            ->where(fn (Builder $range) => $range->where('to_score', '>=', $score)->orWhereNull('to_score'))
            ->orderByDesc('from_score');
    }

    /**
     * @return BelongsTo<Document, $this>
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * @return HasMany<PatientDocumentScoreFinished, $this>
     */
    public function finished(): HasMany
    {
        return $this->hasMany(PatientDocumentScoreFinished::class);
    }

    /**
     * @return HasMany<DocumentScoreAction, $this>
     */
    public function actions(): HasMany
    {
        return $this->hasMany(DocumentScoreAction::class);
    }
}
