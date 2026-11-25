<?php

namespace App\Enums\Contracts;

/**
 * A value the lists show as a coloured badge (<x-status-badge>).
 */
interface HasBadge extends HasLabel
{
    /** The Tabler classes that colour the badge, e.g. "bg-success-lt". */
    public function badgeClass(): string;
}
