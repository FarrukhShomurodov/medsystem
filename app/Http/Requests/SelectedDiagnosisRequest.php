<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Toggles a favourite diagnosis of the signed-in admin; the admin is never taken from the request body.
 */
class SelectedDiagnosisRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'diagnosis_id' => 'required|int|exists:diagnoses,id',
        ];
    }
}
