<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The main warehouse's refusal of a department's supply request, with an optional reason.
 */
class RejectSupplyRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['reason' => 'причина отказа'];
    }

    public function reason(): ?string
    {
        $reason = $this->string('reason')->trim()->value();

        return $reason === '' ? null : $reason;
    }
}
