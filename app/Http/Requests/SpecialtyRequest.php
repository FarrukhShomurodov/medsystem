<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SpecialtyRequest extends FormRequest
{
    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('specialties', 'name')->ignore($this->route('specialty'))],
        ];
    }

    /**
     * @return array{name: string}
     */
    public function specialty(): array
    {
        return [
            'name' => $this->string('name')->value(),
        ];
    }
}
