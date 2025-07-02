<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientDocumentScoreRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'document_response_id' => ['required', 'integer', 'exists:document_responses,id'],
            'document_score_id' => ['required', 'integer', 'exists:document_scores,id'],
            'document_action_id' => ['required', 'integer', 'exists:document_score_actions,id'],
        ];
    }
}
