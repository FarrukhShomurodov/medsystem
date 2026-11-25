<?php

namespace App\Http\Requests\Scheduling;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The week shown by the doctor's calendar: ?start=Y-m-d, the current week by default.
 */
class AppointmentWeekRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'start' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function start(): CarbonImmutable
    {
        $start = $this->date('start', 'Y-m-d');

        return $start?->toImmutable()->startOfDay() ?? CarbonImmutable::today()->startOfWeek(CarbonInterface::MONDAY);
    }
}
