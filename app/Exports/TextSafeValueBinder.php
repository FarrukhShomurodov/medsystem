<?php

namespace App\Exports;

use Maatwebsite\Excel\DefaultValueBinder;
use Override;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

/**
 * Binds the cells of an export as the default binder does (a price stays a number Excel can add
 * up), except text that starts with "=". The default binder stores such text as a formula, which
 * Excel evaluates when the file is opened, and the names and currencies in an export are free text
 * typed into the dashboard. They are stored as text, with the quote prefix Excel shows for a
 * literal "=".
 */
final class TextSafeValueBinder extends DefaultValueBinder
{
    #[Override]
    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (is_string($value) && str_starts_with($value, '=')) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            $cell->getStyle()->setQuotePrefix(true);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
