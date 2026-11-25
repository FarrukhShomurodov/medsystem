<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Procedures ordered for a patient: each one picked from the procedure catalogue (which holds
 * its price) with the date it is planned for.
 */
class PatientProcedureRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'procedures' => ['required', 'array', 'min:1'],
            'procedures.*' => ['required', 'integer', 'exists:procedures,id'],
            'procedure_dates' => ['required', 'array', 'size:'.count((array) $this->input('procedures', []))],
            'procedure_dates.*' => ['required', 'date'],
        ];
    }

    /**
     * The procedures and their dates, paired by position.
     *
     * @return array{patient_id: int, procedures: list<int>, procedure_dates: list<string>}
     */
    public function orders(): array
    {
        return [
            'patient_id' => $this->integer('patient_id'),
            'procedures' => array_values(array_map(intval(...), $this->array('procedures'))),
            'procedure_dates' => array_values(array_map(strval(...), $this->array('procedure_dates'))),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'patient_id' => 'пациент',
            'procedures' => 'процедуры',
            'procedures.*' => 'процедура',
            'procedure_dates' => 'даты процедур',
            'procedure_dates.*' => 'дата процедуры',
        ];
    }
}
