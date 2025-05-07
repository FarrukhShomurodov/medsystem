<?php

namespace App\Http\Requests;

use App\Models\Reception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceptionRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => 'required|int|exists:patients,id',
            'staff_id' => 'required|int|exists:staff,id',
            'start_time' => 'required',
            'end_time' => 'required',
            'date' => 'required|date',
            'comment' => 'nullable|string',
            'status' => ['required', Rule::in(Reception::STATUS)],
        ];
    }
}
