<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SelectedDiagnosisRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'admin_id' => 'required|int|exists:admins,id',
            'diagnosis_id' => 'required|int|exists:diagnoses,id'
        ];
    }
}
