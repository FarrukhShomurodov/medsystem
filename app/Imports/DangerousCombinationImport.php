<?php

namespace App\Imports;

use App\Imports\Concerns\ReadsLayoutColumns;
use App\Imports\Concerns\ReportsResult;
use App\Models\DangerousCombination;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithColumnLimit;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * The drug interaction list: a heading row (row 1) "№ | Drug1 | Препарат 1 | Drug2 | Препарат 2 |
 * Severity | Тяжесть | Interaction/Comment | Взаимодействие/Комментарии", data from row 2. The
 * first data row is a real pair (WithHeadingRow already consumed the headings), so nothing is
 * dropped. An older sample calls the Russian severity "Серьёзность" and adds Mechanism/Механизм
 * columns; those are read too when its headings are in row 1 (the sample left row 1 empty, so
 * all of its rows are skipped and reported as missing the drug names).
 *
 * The prescription check matches the Russian drug names exactly, so names are trimmed. The list
 * names some pairs more than once with a different severity or comment (carbamazepine + irinotecan
 * is both "Противопоказано" and "Тяжёлое"): every distinct row is kept, so no warning is merged
 * into a milder one. A row already in the list is not added again, so a list can be re-imported.
 */
class DangerousCombinationImport implements SkipsEmptyRows, SkipsOnFailure, ToCollection, WithColumnLimit, WithHeadingRow, WithValidation
{
    use ReadsLayoutColumns;
    use ReportsResult;

    /** The columns of a row; a row equal to one already in the list, column for column, is not added. */
    private const array COLUMNS = [
        'drug1_en', 'drug1_ru', 'drug2_en', 'drug2_ru', 'severity_en', 'severity_ru',
        'mechanism_en', 'mechanism_ru', 'comment_en', 'comment_ru',
    ];

    private const int INSERT_CHUNK = 500;

    /**
     * The list is read once and the new rows are inserted in chunks, so an import costs a few
     * queries however long the file is. A lookup per row scanned the whole table every time.
     *
     * @param  Collection<int, Collection<string, mixed>>  $collection
     */
    public function collection(Collection $collection): void
    {
        $known = [];

        foreach (DangerousCombination::query()->toBase()->get(self::COLUMNS) as $stored) {
            $known[$this->key((array) $stored)] = true;
        }

        $now = now();
        $new = [];

        foreach ($collection as $row) {
            $combination = [
                'drug1_en' => $row['drug1'],
                'drug1_ru' => $row['preparat_1'],
                'drug2_en' => $row['drug2'],
                'drug2_ru' => $row['preparat_2'],
                'severity_en' => $row['severity'],
                'severity_ru' => $row['tiazest'] ?? $row['sereznost'],
                'mechanism_en' => $row['mechanism'],
                'mechanism_ru' => $row['mexanizm'],
                'comment_en' => $row['interactioncomment'],
                'comment_ru' => $row['vzaimodeistviekommentarii'],
            ];
            $key = $this->key($combination);

            if (! isset($known[$key])) {
                $known[$key] = true;
                $new[] = [...$combination, 'created_at' => $now, 'updated_at' => $now];
            }

            $this->imported++;
        }

        foreach (array_chunk($new, self::INSERT_CHUNK) as $chunk) {
            DangerousCombination::query()->insert($chunk);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'drug1' => ['nullable', 'string', 'max:255'],
            'preparat_1' => ['required', 'string', 'max:255'],
            'drug2' => ['nullable', 'string', 'max:255'],
            'preparat_2' => ['required', 'string', 'max:255'],
            'severity' => ['nullable', 'string', 'max:255'],
            'tiazest' => ['nullable', 'string', 'max:255'],
            'sereznost' => ['nullable', 'string', 'max:255'],
            'mechanism' => ['nullable', 'string', 'max:255'],
            'mexanizm' => ['nullable', 'string', 'max:255'],
            'interactioncomment' => ['nullable', 'string'],
            'vzaimodeistviekommentarii' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function customValidationAttributes(): array
    {
        return [
            'drug1' => '«Drug1»',
            'preparat_1' => '«Препарат 1»',
            'drug2' => '«Drug2»',
            'preparat_2' => '«Препарат 2»',
            'severity' => '«Severity»',
            'tiazest' => '«Тяжесть»',
            'sereznost' => '«Серьёзность»',
            'mechanism' => '«Mechanism»',
            'mexanizm' => '«Механизм»',
        ];
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    public function prepareForValidation(array $data): array
    {
        foreach (array_keys($this->rules()) as $column) {
            $value = $data[$column] ?? null;
            $text = is_scalar($value) ? trim((string) $value) : '';
            $isComment = in_array($column, ['interactioncomment', 'vzaimodeistviekommentarii'], true);

            $data[$column] = match (true) {
                $text === '' => null,
                $isComment => $text,
                default => Str::squish($text),
            };
        }

        return $data;
    }

    /**
     * Two rows are the same when each column holds the same text, as PostgreSQL's "=" compares it.
     * An empty column (null) differs from any text, so rows that differ only in which column is
     * empty stay two rows.
     *
     * @param  array<string, mixed>  $combination
     */
    private function key(array $combination): string
    {
        return json_encode(
            array_map(fn (string $column): mixed => $combination[$column], self::COLUMNS),
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );
    }
}
