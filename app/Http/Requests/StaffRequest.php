<?php

namespace App\Http\Requests;

use App\Enums\AbsenceReason;
use App\Enums\AbsenceType;
use App\Enums\Gender;
use App\Enums\QualificationCategory;
use App\Enums\ScheduleType;
use App\Models\Staff;
use App\Services\Scheduling\SlotGrid;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StaffRequest extends FormRequest
{
    /** Time inputs send "H:i"; a value rendered from a time column comes back as "H:i:s". */
    private const string TIME = 'date_format:H:i,H:i:s';

    /**
     * The form edits the e-mail, password and role of the employee's login: AdminPolicy::update()
     * for an employee who has one, before validation.
     */
    public function authorize(): Response
    {
        $account = $this->editedStaff()?->admin;

        return $account === null ? Response::allow() : Gate::inspect('update', $account);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $admin = $this->editedStaff()?->admin;

        return [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'date_birthday' => 'required|date',
            'gender' => ['required', Rule::enum(Gender::class)],
            'citizenship' => 'required|string',

            'avatar_url' => 'sometimes|image|mimes:jpg,png,webp|max:2048',
            'sign_url' => 'sometimes|image|mimes:jpg,png,webp|max:2048',

            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',
            'emergency_contact_relation' => 'nullable|string|max:255',

            'position_id' => 'required|integer|exists:staff_positions,id',
            'addition_position_id' => 'nullable|integer|different:position_id|exists:staff_positions,id',
            'speciality_id' => 'required|integer|exists:specialties,id',
            'department_id' => 'required|integer|exists:departments,id',
            'category' => ['required', Rule::enum(QualificationCategory::class)],

            'education_name' => 'nullable|string|max:255',
            'education_graduation_year' => 'nullable|string|max:255',

            'certificate_name' => 'nullable|string|max:255',
            'certificate_number' => 'nullable|string|max:255',
            'certificate_issue_date' => 'nullable|date',
            'certificate_validity_period' => 'nullable|string|max:255',
            'services' => 'nullable|array',
            'services.*' => 'integer|distinct|exists:services,id',

            'employment_contract_number' => 'required|string|max:255',
            'employment_contract_date_of_signing' => 'required|date',
            'employment_contract_validity_period' => 'required|string|max:255',

            'dismissal_orders_number' => 'nullable|string|max:255',
            'dismissal_orders_date' => 'nullable|date',
            'dismissal_reception_number' => 'nullable|string|max:255',
            'dismissal_reception_date' => 'nullable|date',

            // The length of one appointment slot; required when the employee has reception hours (after()).
            'admission_period' => ['nullable', 'integer', 'min:'.SlotGrid::MIN_MINUTES, 'max:'.SlotGrid::MAX_MINUTES],

            'passport_series' => 'required|string|max:255',
            'INN' => 'required|string|max:255',
            'SNILS' => 'required|string|max:255',

            // schedule
            'days' => ['required', 'array'],
            'days.*.id' => ['required', 'integer', 'exists:day_of_weeks,id'],

            'days.*.work' => ['nullable', 'array'],
            'days.*.work.*.start_time' => ['required', self::TIME],
            'days.*.work.*.end_time' => ['required', self::TIME, 'after:days.*.work.*.start_time'],

            'days.*.reception' => ['nullable', 'array'],
            'days.*.reception.*.start_time' => ['required', self::TIME],
            'days.*.reception.*.end_time' => ['required', self::TIME, 'after:days.*.reception.*.start_time'],

            // absence[]: one day as "Y-m-d", a period as "Y-m-d to Y-m-d" (range picker), split into
            // start_date and end_date by prepareForValidation()
            'absence' => 'array|nullable',
            'absence.*.type' => ['required', Rule::enum(AbsenceType::class)],
            'absence.*.date' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}( to \d{4}-\d{2}-\d{2})?$/'],
            'absence.*.start_date' => ['required', 'date_format:Y-m-d'],
            'absence.*.end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:absence.*.start_date'],
            'absence.*.reason' => ['required', Rule::enum(AbsenceReason::class)],

            // surveys[]
            'surveys' => 'nullable|array',
            'surveys.*' => 'required|int|exists:surveys,id',

            // the login account; a new one needs a password
            'login_email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin)],
            'password' => [Rule::requiredIf($admin === null), 'nullable', 'string', Password::min(8)],
        ];
    }

    /**
     * The picker's text is checked as real calendar dates: a date like 2026-02-30 used to be stored
     * as the next valid day, and a period ending before it starts covered no day at all, so the
     * employee stayed bookable while away.
     */
    protected function prepareForValidation(): void
    {
        $absences = $this->input('absence');

        if (! is_array($absences)) {
            return;
        }

        $this->merge(['absence' => array_map(function (mixed $absence): mixed {
            if (! is_array($absence) || ! is_string($absence['date'] ?? null)) {
                return $absence;
            }

            [$start, $end] = array_pad(explode(' to ', $absence['date'], 2), 2, null);

            return [...$absence, 'start_date' => $start, 'end_date' => $end];
        }, $absences)]);
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $hasReceptionHours = $this->collect('days')
                    ->contains(fn (mixed $day): bool => is_array($day) && ! empty($day[ScheduleType::Reception->value]));

                if ($hasReceptionHours && blank($this->input('admission_period'))) {
                    $validator->errors()->add('admission_period', 'Укажите период приёма: у сотрудника есть часы приёма.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'days.*.*.*.end_time.after' => 'Конец интервала должен быть позже его начала.',
            'absence.*.date.regex' => 'Укажите дату нерабочего дня или период.',
            'absence.*.start_date.date_format' => 'Такой даты нерабочего дня нет в календаре.',
            'absence.*.end_date.date_format' => 'Такой даты окончания периода нет в календаре.',
            'absence.*.end_date.after_or_equal' => 'Период отсутствия не может заканчиваться раньше, чем начинается.',
        ];
    }

    private function editedStaff(): ?Staff
    {
        $staff = $this->route('staff');

        return $staff instanceof Staff ? $staff : null;
    }
}
