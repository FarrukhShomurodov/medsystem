<?php

namespace App\Http\Requests\Auth;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Dashboard sign-in. Attempts are limited by the "login" rate limiter (AppServiceProvider).
 */
class LoginRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => 'Логин',
            'password' => 'Пароль',
        ];
    }

    /**
     * @return array{email: string, password: string}
     */
    public function credentials(): array
    {
        return [
            'email' => $this->string('email')->value(),
            'password' => $this->string('password')->value(),
        ];
    }

    /**
     * Signs in an active account (Admin::isActive()). A dismissed employee's password still
     * matches; only then are they told that their login was switched off, so the message reveals
     * nothing to someone guessing passwords.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $guard = Auth::guard(Admin::GUARD);

        if ($guard->attempt([...$this->credentials(), $this->onlyActive(...)], $this->boolean('remember'))) {
            return;
        }

        throw ValidationException::withMessages([
            'login' => $guard->validate($this->credentials()) ? __('auth.inactive') : __('auth.failed'),
        ]);
    }

    /**
     * A condition on the account lookup of the sign-in (a closure among the credentials).
     *
     * @param  Builder<Admin>  $accounts
     */
    private function onlyActive(Builder $accounts): void
    {
        $accounts->active();
    }
}
