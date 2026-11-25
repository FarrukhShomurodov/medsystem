<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreRequest extends FormRequest
{
    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admins,email'],
            'password' => ['required', 'string', Password::min(8)],
        ];
    }

    /**
     * @return array{email: string, password: string}
     */
    public function account(): array
    {
        return [
            'email' => $this->string('email')->value(),
            'password' => $this->string('password')->value(),
        ];
    }
}
