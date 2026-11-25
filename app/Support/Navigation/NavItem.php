<?php

namespace App\Support\Navigation;

use InvalidArgumentException;

/**
 * One entry of the dashboard sidebar: a link to a page, or a group of such links.
 *
 * A link carries the permission its route's middleware requires, so the sidebar shows it to exactly
 * the accounts that may open the page (tests/Feature/Authorization/MenuTest.php and
 * tests/Feature/Frontend/NavigationTest.php hold that true). A group has no route of its own: it is
 * shown when at least one of its links is.
 */
final readonly class NavItem
{
    /**
     * @param  list<string>  $active  route-name patterns (request()->routeIs()) of the pages that
     *                                highlight the link; the link's own route when empty
     * @param  list<NavItem>  $children
     */
    private function __construct(
        public string $label,
        public ?string $icon,
        public ?string $route,
        public ?string $permission,
        public array $active,
        public array $children,
    ) {}

    /**
     * @param  string  $route  name of a GET route without parameters
     * @param  string  $permission  the permission the route's middleware requires
     * @param  list<string>  $active
     */
    public static function link(string $label, string $route, string $permission, array $active = [], ?string $icon = null): self
    {
        return new self($label, $icon, $route, $permission, $active === [] ? [$route] : $active, []);
    }

    /**
     * @param  list<NavItem>  $children
     */
    public static function group(string $label, string $icon, array $children): self
    {
        foreach ($children as $child) {
            if ($child->isGroup()) {
                throw new InvalidArgumentException("The sidebar has two levels: {$label} cannot hold the group {$child->label}.");
            }
        }

        return new self($label, $icon, null, null, [], $children);
    }

    public function isGroup(): bool
    {
        return $this->route === null;
    }

    /**
     * The links of the item: itself, or its children.
     *
     * @return list<NavItem>
     */
    public function links(): array
    {
        return $this->isGroup() ? $this->children : [$this];
    }
}
