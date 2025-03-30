<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttachSurveyRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'staff_id' => 'required|int|exists:staff,id',
            'surveys' => 'nullable|array',
            'surveys.*' => 'required|int|exists:surveys,id',
        ];
    }
}
