<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A recommended action of a score range that the nurse marked as done for a filled-in document.
 */
class PatientDocumentScoreFinished extends Model
{
    protected $fillable = [
        'document_response_id',
        'document_score_id',
        'document_action_id',
    ];

    /**
     * Marking an action done twice (a double click, a retried request) keeps one record.
     *
     * @param  array{document_response_id: int, document_score_id: int, document_action_id: int}  $data
     */
    public static function markDone(array $data): self
    {
        return self::query()->firstOrCreate([
            'document_response_id' => (int) $data['document_response_id'],
            'document_score_id' => (int) $data['document_score_id'],
            'document_action_id' => (int) $data['document_action_id'],
        ]);
    }
}
