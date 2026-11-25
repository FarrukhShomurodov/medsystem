<?php

namespace App\Enums\Contracts;

/**
 * A value the compact lists show as an icon with its label as the tooltip (<x-status-icon>).
 */
interface HasIcon extends HasLabel
{
    /** The boxicons classes of the icon, colour included, e.g. "bx bx-test-tube text-warning". */
    public function icon(): string;
}
