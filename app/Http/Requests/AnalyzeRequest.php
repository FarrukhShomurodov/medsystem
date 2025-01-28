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
            'doctor_id' => 'required|int|exists:doctors,id',
            'analysis_time' => 'required',
            'expected_result_date' => 'required|date',
            'medical_staff' => ['required', Rule::in(Analyze::MEDICAL_STAFF)],
            'target' => 'required|string',
            'status' => ['required', Rule::in(Analyze::STATUS)],
            'notes' => 'required|string',

            'types' => 'required|array',
            'methods' => 'required|array',
            'equipments' => 'required|array',
        ];
    }
}
