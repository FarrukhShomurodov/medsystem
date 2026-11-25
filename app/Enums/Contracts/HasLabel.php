<?php

namespace App\Enums\Contracts;

/**
 * A value the dashboard shows by its Russian name.
 */
interface HasLabel
{
    public function label(): string;
}
