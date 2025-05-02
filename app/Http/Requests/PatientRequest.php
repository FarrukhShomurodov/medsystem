<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            // Основные данные
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'birth_date' => 'required|date|date_format:Y-m-d',
            'gender' => ['required', Rule::in(['male', 'female'])],
            'citizenship' => 'required|string|max:255',
            'passport_series' => 'required|string|max:255',
            'passport_validity_period' => 'required|date|date_format:Y-m-d',

            // Контактная информация
            'phone' => 'required|string|max:255',
            'additional_phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'region_id' => 'required',
            'city_id' => 'required',
            'residential_address_street' => 'required|string|max:255',
            'residential_address_house' => 'required|string|max:255',
            'residential_address_apartment' => 'required|string|max:255',
            'residential_postcode' => 'nullable|int',

            // Адрес регистрации
            'registered_region_id' => 'nullable',
            'registered_city_id' => 'nullable',
            'registered_address_street' => 'nullable|string|max:255',
            'registered_address_house' => 'nullable|string|max:255',
            'registered_address_apartment' => 'nullable|string|max:255',
            'registered_postcode' => 'nullable|int',


            // Страховка
            'insurance_policy_number' => 'nullable|string|max:255',
            'insurance_company' => 'nullable|string|max:255',
            'insurance_type' => 'nullable|string|max:255',
            'insurance_expiry_date' => 'nullable|date|date_format:Y-m-d',

            // Данные экстренных контакто
            'emergency_contact_full_name' => 'required|string|max:255',
            'emergency_contact_relation' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:255',
            'emergency_contact_address' => 'nullable|string|max:255',

            // Медицинская информация
//            'medical_history' => 'nullable|string|max:255',
//            'blood_type' => ['required', 'in:' . implode(',', Patient::BLOOD_TYPES)],
//            'rh_factor' => 'nullable|string|max:255',
//            'special_instructions' => 'nullable|string|max:255',
//
//            'passport_scan' => 'nullable|string|max:255',
//            'insurance_policy_scan' => 'nullable|string|max:255',
//            'other_scans' => 'nullable|string|max:255',
//            'notes' => 'nullable|string|max:255',
//
//            'allegories' => 'required|array',
//            'medical_devices' => 'required|array',
//            'chronic_diseases' => 'required|array',

            'files' => 'sometimes',
        ];
    }
}
