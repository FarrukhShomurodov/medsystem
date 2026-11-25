<?php

namespace App\Http\Requests;

use App\Enums\ReceptionStatus;
use App\Models\Admin;
use App\Models\Reception;
use App\Models\Staff;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Booking an appointment from the reception calendars.
 */
class ReceptionRequest extends FormRequest
{
    /**
     * ReceptionPolicy for the employee to book, before validation: a doctor's account books only its
     * own calendar. The employee is named in the body, not in the URL, so the route cannot ask the
     * policy. An id that names nobody is asked about as an unsaved employee, so a doctor is refused
     * it like a colleague's and cannot tell which ids exist; everyone else gets the exists rule.
     */
    public function authorize(): Response
    {
        $id = $this->integer('staff_id');
        $staff = Staff::query()->find($id) ?? (new Staff)->forceFill(['id' => $id]);

        return Gate::inspect('workOnCalendar', [Reception::class, $staff]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'staff_id' => ['required', 'integer', 'exists:staff,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'status' => [
                'nullable',
                Rule::enum(ReceptionStatus::class)->only(ReceptionStatus::initial()),
                // Taking the payment while booking is a cash desk action, like every other payment.
                Rule::when(! $this->user(Admin::GUARD)?->can('accept_payments'), [Rule::notIn([ReceptionStatus::Paid->value])]),
            ],
        ];
    }

    /**
     * @return array{patient_id: int, staff_id: int, service_id: int, date: string, start_time: string, end_time: string, comment: string|null, status: string|null}
     */
    public function booking(): array
    {
        return [
            'patient_id' => $this->integer('patient_id'),
            'staff_id' => $this->integer('staff_id'),
            'service_id' => $this->integer('service_id'),
            'date' => $this->string('date')->value(),
            'start_time' => $this->string('start_time')->value(),
            'end_time' => $this->string('end_time')->value(),
            'comment' => $this->filled('comment') ? $this->string('comment')->value() : null,
            'status' => $this->filled('status') ? $this->string('status')->value() : null,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.not_in' => 'Отметить приём оплаченным может только касса.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'patient_id' => 'пациент',
            'staff_id' => 'врач',
            'service_id' => 'услуга',
            'date' => 'дата приёма',
            'start_time' => 'время начала',
            'end_time' => 'время окончания',
            'comment' => 'комментарий',
            'status' => 'статус',
        ];
    }
}
