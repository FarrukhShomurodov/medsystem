<?php

namespace App\Http\Requests\Scheduling;

use App\Enums\ReceptionStatus;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The filters of the reception list. The dashboard's appointment cards open it on their day
 * (?date=Y-m-d). Authorization is the route's: it requires list_receptions.
 */
class ReceptionFilterRequest extends FormRequest
{
    /** Invalid filters lead back to the unfiltered list, with the errors shown. */
    protected $redirectRoute = 'receptions.index';

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'staff_id' => ['nullable', 'integer'],
            'is_disposable' => ['nullable', Rule::in(['yes', 'no'])],
            'status' => ['nullable', Rule::enum(ReceptionStatus::class)],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'staff_id' => 'сотрудник',
            'is_disposable' => 'разовый приём',
            'status' => 'статус',
            'date' => 'дата',
        ];
    }

    public function staffId(): ?int
    {
        return $this->filled('staff_id') ? $this->integer('staff_id') : null;
    }

    /** Whether the appointments belong to no examination map (yes), to one (no), or either (null). */
    public function disposable(): ?bool
    {
        return $this->filled('is_disposable') ? $this->input('is_disposable') === 'yes' : null;
    }

    public function status(): ?ReceptionStatus
    {
        return $this->enum('status', ReceptionStatus::class);
    }

    public function day(): ?CarbonImmutable
    {
        return $this->date('date', 'Y-m-d')?->toImmutable();
    }
}
