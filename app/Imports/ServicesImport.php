<?php

namespace App\Imports;

use App\Imports\Concerns\ReadsLayoutColumns;
use App\Imports\Concerns\ReportsResult;
use App\Models\Service;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithColumnLimit;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * The price list: one service per row under a heading row (row 1) with the columns
 * Наименование, Описание, Код, Стоимость, Скидка, Валюта, Тип ID, Группа ID, Активна, По записи.
 * Стоимость is the price the cash desk charges, VAT included, stored as given, as the form stores
 * it (ServiceService). The headings arrive slugged (naimenovanie, tip_id, ...). Rows that fail
 * validation are skipped and reported; the valid rows are saved in the import's transaction.
 */
class ServicesImport implements SkipsEmptyRows, SkipsOnFailure, ToCollection, WithColumnLimit, WithHeadingRow, WithValidation
{
    use ReadsLayoutColumns;
    use ReportsResult;

    /**
     * @param  Collection<int, Collection<string, mixed>>  $collection
     */
    public function collection(Collection $collection): void
    {
        foreach ($collection as $row) {
            Service::query()->create([
                'name' => $row['naimenovanie'],
                'description' => $row['opisanie'] ?? null,
                'code' => $row['kod'] ?? null,
                'price' => $row['stoimost'],
                'discount' => $row['skidka'] ?? null,
                'currency' => $row['valiuta'] ?? null,
                'service_type_id' => (int) $row['tip_id'],
                'service_group_id' => (int) $row['gruppa_id'],
                'is_active' => $row['aktivna'] ?? true,
                'is_appointment' => $row['po_zapisi'] ?? false,
            ]);

            $this->imported++;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'naimenovanie' => ['required', 'string', 'max:255'],
            'opisanie' => ['nullable', 'string', 'max:255'],
            'kod' => ['nullable', 'string', 'max:255'],
            'stoimost' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'skidka' => ['nullable', 'integer', 'min:0'],
            'valiuta' => ['nullable', 'string', 'max:255'],
            'tip_id' => ['required', 'integer', 'exists:service_types,id'],
            'gruppa_id' => ['required', 'integer', 'exists:service_groups,id'],
            'aktivna' => ['nullable', 'boolean'],
            'po_zapisi' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function customValidationAttributes(): array
    {
        return [
            'naimenovanie' => '«Наименование»',
            'opisanie' => '«Описание»',
            'kod' => '«Код»',
            'stoimost' => '«Стоимость»',
            'skidka' => '«Скидка»',
            'valiuta' => '«Валюта»',
            'tip_id' => '«Тип ID»',
            'gruppa_id' => '«Группа ID»',
            'aktivna' => '«Активна»',
            'po_zapisi' => '«По записи»',
        ];
    }

    /**
     * Spreadsheet cells come typed: a numeric code arrives as a number, a yes/no column as text.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForValidation(array $data): array
    {
        foreach (['naimenovanie', 'opisanie', 'kod', 'valiuta'] as $text) {
            if (isset($data[$text]) && is_scalar($data[$text])) {
                $data[$text] = trim((string) $data[$text]);
            }
        }

        foreach (['aktivna', 'po_zapisi'] as $flag) {
            $data[$flag] = $this->yesNo($data[$flag] ?? null);
        }

        return $data;
    }

    private function yesNo(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return match (mb_strtolower(trim($value))) {
            'да' => true,
            'нет' => false,
            '' => null,
            default => $value,
        };
    }
}
