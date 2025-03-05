<?php

namespace App\Http\Requests;

use App\Models\Analyze;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalyzeRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => 'required|int|exists:patients,id',
            'examination_map_id' => 'nullable|int|exists:examination_maps,id',
            'laboratory_id' => 'required',
            'staff_id' => 'required|int|exists:staff,id',
            'analysis_time' => 'required',
            'expected_result_date' => 'nullable|date',
            'medical_staff' => ['nullable', Rule::in(Analyze::MEDICAL_STAFF)],
            'target' => 'nullable|string',
            'status' => ['required', Rule::in(Analyze::STATUS)],
            'notes' => 'nullable|nullable',

            'types' => 'required|array',
            'methods' => 'required|array',
            'equipments' => 'required|array',
        ];
    }
}
