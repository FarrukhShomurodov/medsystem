<?php

namespace App\Http\Requests\Admin;

use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateRequest extends FormRequest
{
    /**
     * AdminPolicy::update(), before validation: the errors of a refused form would tell which
     * e-mail addresses are taken.
     */
    public function authorize(): Response
    {
        return Gate::inspect('update', $this->route('admin'));
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($this->route('admin'))],
            'password' => ['nullable', 'string', Password::min(8)],
        ];
    }

    /**
     * The password is null when the field was left empty (AdminService::update() keeps the current one).
     *
     * @return array{email: string, password: string|null}
     */
    public function account(): array
    {
        return [
            'email' => $this->string('email')->value(),
            'password' => $this->filled('password') ? $this->string('password')->value() : null,
        ];
    }
}
