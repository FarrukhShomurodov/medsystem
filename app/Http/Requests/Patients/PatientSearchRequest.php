<?php

namespace App\Http\Requests\Patients;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The patient search of the booking dialogs. select2 sends the typed text twice, as term and as q.
 */
class PatientSearchRequest extends FormRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'term' => 'nullable|string|max:100',
            'q' => 'nullable|string|max:100',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'term' => 'поиск',
            'q' => 'поиск',
        ];
    }

    /** The typed text, trimmed; empty when nothing was typed yet. */
    public function term(): string
    {
        return $this->string($this->filled('term') ? 'term' : 'q')->trim()->value();
    }
}
