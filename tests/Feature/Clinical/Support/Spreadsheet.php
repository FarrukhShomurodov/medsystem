<?php

namespace Tests\Feature\Clinical\Support;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Workbook;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * A small .xlsx upload built in the test, laid out like the files users import.
 */
final class Spreadsheet
{
    /**
     * @param  list<list<string|int|float|bool|null>>  $rows  the sheet from row 1, cells from column A
     * @param  string|null  $usedUpTo  a column far to the right that holds a stray cell in row 1, as
     *                                 in sheets formatted across the whole width (the interaction
     *                                 list is used up to XFD)
     */
    public static function upload(array $rows, string $name = 'import.xlsx', ?string $usedUpTo = null): UploadedFile
    {
        $workbook = new Workbook;
        $sheet = $workbook->getActiveSheet();
        $sheet->fromArray($rows, strictNullComparison: true);

        if ($usedUpTo !== null) {
            $sheet->setCellValue($usedUpTo.'1', 'Formatting');
        }

        $path = tempnam(sys_get_temp_dir(), 'import');
        (new Xlsx($workbook))->save($path);

        return new UploadedFile($path, $name, test: true);
    }
}
