<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatientInspectionRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => 'required|int|exists:patients,id',
            'bp' => 'required|string',
            'pulse' => 'required|string',
            'saturation' => 'required|string',
            'temperature' => 'required|string',
            'chdd' => 'required|string',
            'consciousness_level' => 'required|string',
            'oxygen_status' => 'required|string',
        ];
    }
}
