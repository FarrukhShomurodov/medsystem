<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeResultRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'result' => 'required|array',
            'result.*.type_id' => 'required',
            'result.*.unit' => 'required',
            'result.*.value' => 'required',
            'result.*.reference_range' => 'required',
            'result.*.comment' => 'nullable',
        ];
    }
}
