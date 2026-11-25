<?php

namespace App\Http\Requests\Patients;

use App\Models\VitalParameter;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Values of vital parameters keyed by parameter id: the normal values of a patient (patient card)
 * and a measurement (nurse window). Empty inputs are left out.
 */
class VitalParametersRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'parameters' => 'required|array',
            // decimal(8, 1) columns
            'parameters.*' => 'nullable|numeric|between:-9999999.9,9999999.9',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'parameters' => 'Показатели',
            'parameters.*' => 'Показатель',
        ];
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $ids = array_keys((array) $this->input('parameters', []));
                // Only ids a bigint holds reach the query: PostgreSQL rejects a larger literal with an error.
                $validIds = array_filter($ids, fn (int|string $id): bool => filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false);

                if (count($validIds) !== count($ids) || VitalParameter::query()->whereKey($validIds)->count() !== count($ids)) {
                    $validator->errors()->add('parameters', 'Неизвестный показатель.');
                }

                if ($this->filledValues() === []) {
                    $validator->errors()->add('parameters', 'Введите хотя бы один показатель.');
                }
            },
        ];
    }

    /**
     * The filled values, keyed by vital parameter id. A nested array is not a value: the rules
     * reject it, and the after() hook that runs anyway must not turn it into a string.
     *
     * @return array<int, string>
     */
    public function filledValues(): array
    {
        return collect((array) $this->input('parameters', []))
            ->filter(fn (mixed $value): bool => is_scalar($value) && $value !== '')
            ->mapWithKeys(fn (mixed $value, int|string $id): array => [(int) $id => (string) $value])
            ->all();
    }
}
