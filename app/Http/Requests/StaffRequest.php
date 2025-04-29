<?php

namespace App\Http\Requests;

use App\Models\Staff;
use App\Models\StaffProfessionalData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StaffRequest extends FormRequest
{

    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'date_birthday' => 'required|date',
            'gender' => ['required', Rule::in(Staff::GENDER)],

            'phone' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:255',
            'emergency_contact_relation' => 'required|string|max:255',

            'position_id' => 'required',
            'addition_position_id' => 'nullable',
            'speciality_id' => 'required',
            'department_id' => 'required',
            'category' => ['required', Rule::in(StaffProfessionalData::CATEGORY)],

            'education_name' => 'required|string|max:255',
            'education_graduation_year' => 'required|string|max:255',

            'certificate_name' => 'required|string|max:255',
            'certificate_number' => 'required|string|max:255',
            'certificate_issue_date' => 'required|date',
            'certificate_validity_period' => 'required|string|max:255',
            'services' => 'required|array',

            'employment_contract_number' => 'required|string|max:255',
            'employment_contract_date_of_signing' => 'required|date',
            'employment_contract_validity_period' => 'required|string|max:255',

            'dismissal_orders_number' => 'nullable|string|max:255',
            'dismissal_orders_date' => 'nullable|date',
            'dismissal_reception_number' => 'nullable|string|max:255',
            'dismissal_reception_date' => 'nullable|date',

            'admission_period' => 'required|int',

            'passport_series' => 'required|string|max:255',
            'INN' => 'required|string|max:255',
            'SNILS' => 'required|string|max:255',

            // schedule
            'days' => ['required', 'array'],
            'days.*.id' => ['required', 'exists:day_of_weeks,id'],

            // work[]
            'days.*.work' => ['nullable', 'array'],
            'days.*.work.*.start_time' => ['required_with:day.*.work.*.end_time'],
            'days.*.work.*.end_time' => ['required_with:day.*.work.*.start_time', 'after:day.*.work.*.start_time'],

            // reception[]
            'days.*.reception' => ['nullable', 'array'],
            'days.*.reception.*.start_time' => ['required_with:day.*.reception.*.end_time'],
            'days.*.reception.*.end_time' => ['required_with:day.*.reception.*.start_time', 'after:day.*.reception.*.start_time'],


            // absence[]
            'absence' => 'array|nullable',
            'absence.*.type' => 'required_with:absence.*.date,absence.*.reason',
            'absence.*.date' => 'required_with:absence.*.type,absence.*.reason',
            'absence.*.reason' => 'required_with:absence.*.type,absence.*.date',

            // surveys[]
            'surveys' => 'nullable|array',
            'surveys.*' => 'required|int|exists:surveys,id',

            // auth
            'login_email' => 'required|email',
            'password' => 'nullable|min:8',
        ];
    }
}
