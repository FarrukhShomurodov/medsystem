<?php

namespace App\Http\Requests;

use App\Models\Staff;
use App\Models\StaffProfessionalData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffRequest extends FormRequest
{
    /**
     * Asence
     * @param $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->sometimes([
            'absence_start_date',
            'absence_end_date',
            'absence_reason',
            'absence_description',
        ], 'required', function ($input) {
            return $input->absence_start_date || $input->absence_end_date || $input->absence_reason || $input->absence_description;
        });
    }

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
            'dismissal_orders_number' => 'required|string|max:255',
            'dismissal_orders_date' => 'required|date',
            'dismissal_reception_number' => 'required|string|max:255',
            'dismissal_reception_date' => 'required|date',
            'passport_series' => 'required|string|max:255',
            'INN' => 'required|string|max:255',
            'SNILS' => 'required|string|max:255',

            // schedule
            'day_start' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'day_end' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'reception_start' => 'required',
            'reception_end' => 'required|after:reception_start'
        ];
    }
}
