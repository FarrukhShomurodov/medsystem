<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Services\PatientService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientRequest extends FormRequest
{
    public const MAX_FILES = 10;

    public const MAX_FILE_KILOBYTES = 10240;

    /** @var list<string> */
    public const FILE_TYPES = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

    /** A new city needs a region: city field => region field. */
    private const array CITY_REGIONS = [
        'city_id' => 'region_id',
        'registered_city_id' => 'registered_region_id',
    ];

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Основные данные
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'birth_date' => 'required|date_format:Y-m-d|before_or_equal:today',
            'gender' => ['required', Rule::enum(Gender::class)],
            'citizenship' => 'required|string|max:255',
            'passport_series' => 'required|string|max:255',
            'passport_validity_period' => 'required|date|date_format:Y-m-d',
            'referral_source' => 'nullable|string|max:255',

            // Контактная информация
            'phone' => 'required|string|max:255',
            'additional_phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'region_id' => ['nullable', 'integer', Rule::exists('regions', 'id')],
            'region_name' => 'nullable|string|max:255',
            'city_id' => ['nullable', 'integer', Rule::exists('cities', 'id')],
            'city_name' => 'nullable|string|max:255',
            'residential_address_street' => 'nullable|string|max:255',
            'residential_address_house' => 'nullable|string|max:255',
            'residential_address_apartment' => 'nullable|string|max:255',
            // integer columns: a longer number fails in PostgreSQL, a sign or a decimal is no postcode
            'residential_postcode' => 'nullable|digits_between:1,9',

            // Адрес регистрации
            'registered_region_id' => ['nullable', 'integer', Rule::exists('regions', 'id')],
            'registered_region_name' => 'nullable|string|max:255',
            'registered_city_id' => ['nullable', 'integer', Rule::exists('cities', 'id')],
            'registered_city_name' => 'nullable|string|max:255',
            'registered_address_street' => 'nullable|string|max:255',
            'registered_address_house' => 'nullable|string|max:255',
            'registered_address_apartment' => 'nullable|string|max:255',
            'registered_postcode' => 'nullable|digits_between:1,9',

            // Страховка
            'insurance_policy_number' => 'nullable|string|max:255',
            'insurance_company' => 'nullable|string|max:255',
            'insurance_type' => 'nullable|string|max:255',
            'insurance_expiry_date' => 'nullable|date|date_format:Y-m-d',

            // Экстренные контакты
            'emergency_contact_full_name' => 'nullable|string|max:255',
            'emergency_contact_relation' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',
            'emergency_contact_address' => 'nullable|string|max:255',

            // Scans and documents; stored on the private disk (see App\Models\File).
            'files' => ['sometimes', 'array', 'max:'.self::MAX_FILES],
            'files.*' => ['file', 'mimes:'.implode(',', self::FILE_TYPES), 'max:'.self::MAX_FILE_KILOBYTES],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'region_id' => 'Область',
            'region_name' => 'Область',
            'city_id' => 'Город',
            'city_name' => 'Город',
            'registered_region_id' => 'Область регистрации',
            'registered_region_name' => 'Область регистрации',
            'registered_city_id' => 'Город регистрации',
            'registered_city_name' => 'Город регистрации',
            'residential_postcode' => 'Почтовый индекс',
            'registered_postcode' => 'Почтовый индекс регистрации',
            'files' => 'Файлы',
            'files.*' => 'Файл',
        ];
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                foreach (self::CITY_REGIONS as $cityField => $regionField) {
                    $hasRegion = $this->filled($regionField) || $this->filled(PatientService::ADDRESS_LOOKUPS[$regionField]);

                    if ($this->filled(PatientService::ADDRESS_LOOKUPS[$cityField]) && ! $hasRegion) {
                        $validator->errors()->add($cityField, 'Чтобы добавить новый город, выберите его область.');
                    }
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $typedNames = [];

        foreach (PatientService::ADDRESS_LOOKUPS as $idField => $nameField) {
            $value = $this->input($idField);

            if (is_string($value) && trim($value) !== '' && ! ctype_digit($value)) {
                $typedNames[$idField] = null;
                $typedNames[$nameField] = trim($value);
            }
        }

        $this->merge($typedNames);
    }
}
