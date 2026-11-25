<?php

namespace App\Http\Requests\Patients;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The diagnosis search of the patient card's picker: the typed code or words of the name.
 */
class DiagnosisSearchRequest extends FormRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'term' => 'nullable|string|max:100',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'term' => 'поиск',
        ];
    }

    /** The typed text, trimmed; empty when nothing was typed yet. */
    public function term(): string
    {
        return $this->string('term')->trim()->value();
    }
}
