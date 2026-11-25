<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentScoreAction extends Model
{
    protected $fillable = ['document_score_id', 'action'];

    /**
     * @return BelongsTo<DocumentScore, $this>
     */
    public function score(): BelongsTo
    {
        return $this->belongsTo(DocumentScore::class, 'document_score_id');
    }
}
