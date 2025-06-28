<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientProcedureRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => 'required|int|exists:patients,id',
            'procedures' => 'required|array',
            'procedures.*' => 'required|string',
            'procedure_dates' => [
                'required',
                'array',
                Rule::when($this->has('procedures'), [
                    'size:' . count($this->input('procedures', []))
                ])
            ],
            'procedure_dates.*' => 'date',
        ];
    }
}
