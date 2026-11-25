<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiagnosisRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255'],
            // The ICD-10 list has names longer than 255 characters (the column is text).
            'name' => ['required', 'string', 'max:1000'],
            'surveys' => ['nullable', 'array'],
            'surveys.*' => ['required', 'integer', 'exists:surveys,id'],
        ];
    }
}
