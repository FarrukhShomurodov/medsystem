<?php

namespace App\Imports;

use App\Imports\Concerns\ReadsLayoutColumns;
use App\Imports\Concerns\ReportsResult;
use App\Models\Diagnosis;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithColumnLimit;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * The ICD-10 list as the ministry spreadsheet lays it out: a heading row (row 1) "Код | | | | |
 * | Название | Примечание", where the code sits in one of the six code columns A-F by its depth
 * in the classification (class, block, category, subcategory) and "-" marks the tree expander.
 * Empty headings arrive keyed by column index, so the code columns are kod, 1, 2, 3, 4 and 5.
 *
 * A code already in the catalogue gets the new name, so the list can be imported again.
 */
class DiagnosisImport implements SkipsEmptyRows, SkipsOnFailure, ToCollection, WithColumnLimit, WithHeadingRow, WithValidation
{
    use ReadsLayoutColumns;
    use ReportsResult;

    private const array CODE_COLUMNS = ['kod', 1, 2, 3, 4, 5];

    private const int INSERT_CHUNK = 500;

    /**
     * @param  Collection<int, Collection<array-key, mixed>>  $collection
     */
    public function collection(Collection $collection): void
    {
        $existing = Diagnosis::query()->pluck('name', 'code');
        $new = [];

        foreach ($collection as $row) {
            $code = $row['code'];
            $name = $row['name'];

            if (array_key_exists($code, $new) || ! $existing->has($code)) {
                $new[$code] = ['code' => $code, 'name' => $name, 'created_at' => now(), 'updated_at' => now()];
            } elseif ($existing->get($code) !== $name) {
                Diagnosis::query()->where('code', $code)->update(['name' => $name]);
                $existing->put($code, $name);
            }

            $this->imported++;
        }

        foreach (array_chunk(array_values($new), self::INSERT_CHUNK) as $chunk) {
            Diagnosis::query()->insert($chunk);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function customValidationAttributes(): array
    {
        return [
            'code' => '«Код»',
            'name' => '«Название»',
        ];
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    public function prepareForValidation(array $data): array
    {
        $code = collect(self::CODE_COLUMNS)
            ->map(fn (int|string $column): string => trim((string) ($data[$column] ?? '')))
            ->first(fn (string $value): bool => $value !== '' && $value !== '-');

        $data['code'] = $code;
        $data['name'] = isset($data['nazvanie']) ? trim((string) $data['nazvanie']) : null;

        return $data;
    }
}
