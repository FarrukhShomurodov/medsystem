<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentResponseRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'document_id' => 'required|int|exists:documents,id',
            'patient_id' => 'required|int|exists:patients,id',
            'answers' => 'required|array',
            'previous_url' => 'required|string',
        ];
    }
}
