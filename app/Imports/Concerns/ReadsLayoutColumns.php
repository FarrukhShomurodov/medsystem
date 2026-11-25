<?php

namespace App\Imports\Concerns;

/**
 * For imports that implement WithColumnLimit. The uploaded sheets are formatted across the whole
 * width (the interaction list up to column XFD), and reading all 16 384 cells of every row runs
 * out of memory long before the import ends. The layouts use at most ten columns, so the rest of
 * the row is not read.
 */
trait ReadsLayoutColumns
{
    public function endColumn(): string
    {
        return 'Z';
    }
}
