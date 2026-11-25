<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientInspectionRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // The readings are free text (e.g. "120/80"), stored in varchar(255) columns.
        $reading = ['required', 'string', 'max:255'];

        return [
            'patient_id' => ['required', 'integer', Rule::exists('patients', 'id')],
            'bp' => $reading,
            'pulse' => $reading,
            'saturation' => $reading,
            'temperature' => $reading,
            'chdd' => $reading,
            'consciousness_level' => $reading,
            'oxygen_status' => $reading,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'patient_id' => 'Пациент',
            'bp' => 'Артериальное давление',
            'pulse' => 'Пульс',
            'saturation' => 'Сатурация',
            'temperature' => 'Температура',
            'chdd' => 'ЧДД',
            'consciousness_level' => 'Уровень сознания',
            'oxygen_status' => 'Кислород/воздух',
        ];
    }
}
