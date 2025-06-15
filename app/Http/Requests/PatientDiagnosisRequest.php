<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatientDiagnosisRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => 'required|int|exists:patients,id',
            'examination_map_id' => 'required|int|exists:examination_maps,id',
            'description' => 'required|string'
        ];
    }
}
