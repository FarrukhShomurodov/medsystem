<?php

namespace App\Support\Navigation;

/**
 * A titled block of sidebar entries ("Склад и аптека", "Справочники" ...).
 */
final readonly class NavSection
{
    /**
     * @param  list<NavItem>  $items
     */
    public function __construct(
        public string $title,
        public array $items,
    ) {}
}
