<?php

namespace App\Http\Requests\Stock;

use App\Enums\TransferStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The status select of the transfer list. Whether the transfer may move to that status is the
 * workflow's to decide (TransferService::changeStatus()). Authorization is the route's.
 */
class TransferStatusRequest extends FormRequest
{
    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(TransferStatus::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'status' => 'статус',
        ];
    }

    public function status(): TransferStatus
    {
        return TransferStatus::from($this->validated('status'));
    }
}
