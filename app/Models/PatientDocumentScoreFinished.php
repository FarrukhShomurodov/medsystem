<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientDocumentScoreFinished extends Model
{
    protected $fillable = [
        'document_response_id',
        'document_score_id',
        'document_action_id',
    ];

    public static function storeFromRequest(array $data): self
    {
        return self::create([
            'document_response_id' => $data['document_response_id'],
            'document_score_id' => $data['document_score_id'],
            'document_action_id' => $data['document_action_id'],
        ]);
    }
}
