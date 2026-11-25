<?php

namespace App\Http\Requests\Patients;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The filter form of the patient list (also submitted from the patient card).
 */
class PatientFilterRequest extends FormRequest
{
    /**
     * A refused filter goes back to the unfiltered list, not to the previous URL: that is the
     * filtered list itself once the filter was submitted twice, a redirect loop.
     *
     * @var string
     */
    protected $redirectRoute = 'patients.index';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'nullable|integer|min:1',
            'first_name' => 'nullable|string|max:255',
            'second_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date_format:Y-m-d',
            'department_id' => 'nullable|integer',
            'staff_id' => 'nullable|integer',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'id' => 'ID',
            'first_name' => 'Имя',
            'second_name' => 'Фамилия',
            'middle_name' => 'Отчество',
            'birth_date' => 'Дата рождения',
            'department_id' => 'Отделение',
            'staff_id' => 'Врач',
        ];
    }
}
