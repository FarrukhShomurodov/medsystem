<?php

namespace App\Http\Requests\Billing;

use App\Services\Billing\BillableType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The orders ticked on the cashier page, posted as records[] = "service:12".
 * Authorization is the route's: it requires accept_payments.
 */
class PayItemsRequest extends FormRequest
{
    private const int MAX_RECORDS = 500;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $types = implode('|', BillableType::values());

        return [
            'records' => ['required', 'array', 'max:'.self::MAX_RECORDS],
            // Up to 18 digits: the id always fits a PHP integer.
            'records.*' => ['required', 'string', 'distinct', "regex:/^({$types}):[1-9][0-9]{0,17}$/"],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'records.required' => 'Выберите записи для оплаты.',
            'records.*.regex' => 'Выбрана неизвестная запись для оплаты.',
            'records.*.distinct' => 'Запись выбрана дважды.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'records' => 'записи для оплаты',
            'records.*' => 'запись для оплаты',
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                foreach ($this->parse($this->input('records')) as $type => $ids) {
                    $type = BillableType::from($type);
                    $missing = array_diff($ids, $type->query()->whereKey($ids)->pluck('id')->all());

                    if ($missing !== []) {
                        $validator->errors()->add('records', "Записи не найдены: {$type->label()} №".implode(', №', $missing).'.');
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, list<int>> BillableType value => order ids
     */
    public function idsByType(): array
    {
        return $this->parse($this->validated('records'));
    }

    /**
     * @param  list<string>  $records
     * @return array<string, list<int>>
     */
    private function parse(array $records): array
    {
        $ids = [];

        foreach ($records as $record) {
            [$type, $id] = explode(':', $record, 2);
            $ids[$type][] = (int) $id;
        }

        return $ids;
    }
}
