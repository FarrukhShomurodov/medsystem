<?php

namespace App\Http\Requests;

use App\Enums\AdministrationRoute;
use App\Enums\AdmissionRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The prescription form: parallel arrays with one entry per prescribed medication (the edit page
 * sends a single line). A new prescription is always "ordered", so a status sent by the form is
 * ignored; statuses change through their own actions only.
 */
class PatientMedicationRequest extends FormRequest
{
    /** The arrays that hold one value per prescribed medication, under the same keys. */
    private const array PER_LINE = ['dosages', 'single_doses', 'methods', 'admission_rules', 'date_of_ends', 'admission_periods'];

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Every line needs every column: they are NOT NULL in patient_medications.
        $perLine = ['required', 'array', 'size:'.count((array) $this->input('medications', []))];

        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],

            'medications' => ['required', 'list', 'min:1'],
            'medications.*' => ['required', 'integer', 'exists:medications,id'],

            'dosages' => $perLine,
            'dosages.*' => ['required', 'integer', 'min:1', 'max:10000'],

            'single_doses' => $perLine,
            'single_doses.*' => ['required', 'string', 'max:50'],

            'methods' => $perLine,
            'methods.*' => ['required', Rule::enum(AdministrationRoute::class)],

            'admission_rules' => $perLine,
            'admission_rules.*' => ['required', Rule::enum(AdmissionRule::class)],

            'date_of_ends' => $perLine,
            'date_of_ends.*' => ['required', 'date'],

            'admission_periods' => $perLine,
            'admission_periods.*' => ['required', 'string', 'max:100'],

            'medication_notes' => ['nullable', 'array'],
            'medication_notes.*' => ['nullable', 'string', 'max:255'],

            'compatible_medications' => ['nullable', 'array'],
            'compatible_medications.*' => ['array'],
            'compatible_medications.*.*' => ['integer', 'exists:medications,id'],

            'compatible_dosages' => ['nullable', 'array'],
            'compatible_dosages.*' => ['array'],
            'compatible_dosages.*.*' => ['required', 'string', 'max:50'],
        ];
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                // The service reads every array at the keys of the medications: a value under another
                // key belongs to no line, and a line without its value cannot be saved.
                $lines = array_keys((array) $this->input('medications', []));

                foreach (self::PER_LINE as $field) {
                    if (! $validator->errors()->has($field) && array_keys((array) $this->input($field, [])) !== $lines) {
                        $validator->errors()->add($field, "Строки поля «{$this->attributes()[$field]}» не совпадают со строками препаратов.");
                    }
                }

                foreach ((array) $this->input('compatible_medications', []) as $line => $medications) {
                    if (! in_array($line, $lines, true)) {
                        $validator->errors()->add("compatible_medications.{$line}", 'Совместимые препараты указаны для строки, которой нет в назначении.');
                    } elseif (array_keys((array) $medications) !== array_keys((array) $this->input("compatible_dosages.{$line}", []))) {
                        $validator->errors()->add("compatible_dosages.{$line}", 'Укажите дозировку каждого совместимого препарата.');
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'patient_id' => 'пациент',
            'medications' => 'препараты',
            'medications.*' => 'препарат',
            'dosages' => 'дозировки',
            'dosages.*' => 'дозировка (количество единиц)',
            'single_doses' => 'разовые дозы',
            'single_doses.*' => 'разовая доза',
            'methods' => 'способы приёма',
            'methods.*' => 'способ приёма',
            'admission_rules' => 'правила приёма',
            'admission_rules.*' => 'правило приёма',
            'date_of_ends' => 'даты окончания',
            'date_of_ends.*' => 'дата окончания',
            'admission_periods' => 'периоды приёма',
            'admission_periods.*' => 'период приёма',
            'medication_notes.*' => 'примечания',
            'compatible_medications.*.*' => 'совместимый препарат',
            'compatible_dosages.*.*' => 'дозировка совместимого препарата',
        ];
    }
}
