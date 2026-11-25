<?php

use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavItem;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use Tests\Support\DashboardRoutes;

/*
| App\Support\Navigation\Navigation is the only definition of the sidebar. Each link must open an
| existing page and carry exactly the permission that page's middleware checks; MenuTest then
| checks the rendered sidebar for every permission.
*/

function navigationRoute(NavItem $link): ?RoutingRoute
{
    return app(Router::class)->getRoutes()->getByName((string) $link->route);
}

it('links only to existing pages that open without parameters', function (): void {
    $problems = [];

    foreach (Navigation::links() as $link) {
        $route = navigationRoute($link);

        if ($route === null) {
            $problems[] = "{$link->label}: no route named {$link->route}";
        } elseif (! in_array('GET', $route->methods(), true) || $route->parameterNames() !== []) {
            $problems[] = "{$link->label}: {$link->route} is not a GET page without parameters";
        }
    }

    expect($problems)->toBe([])
        ->and(Navigation::links())->not->toBeEmpty();
});

it('gives every link the permission its route requires, and only that one', function (): void {
    $problems = [];

    foreach (Navigation::links() as $link) {
        $route = navigationRoute($link);

        if ($route === null) {
            continue;
        }

        $required = DashboardRoutes::permissionsOf($route);

        if ($required !== [[$link->permission]]) {
            $problems[] = sprintf('%s (%s): the link says %s, the route requires %s', $link->label, $link->route, $link->permission, json_encode($required, JSON_UNESCAPED_UNICODE));
        }
    }

    expect($problems)->toBe([]);
});

it('highlights each link on its own page, and every active pattern names existing routes', function (): void {
    $names = collect(app(Router::class)->getRoutes()->getRoutesByName())->keys();
    $problems = [];

    foreach (Navigation::links() as $link) {
        if (! Str::is($link->active, (string) $link->route)) {
            $problems[] = "{$link->label}: {$link->route} is not among its own active patterns";
        }

        foreach ($link->active as $pattern) {
            if ($names->doesntContain(fn (string $name): bool => Str::is($pattern, $name))) {
                $problems[] = "{$link->label}: the active pattern {$pattern} matches no route";
            }
        }
    }

    expect($problems)->toBe([]);
});

it('highlights exactly one link on every page of the dashboard', function (): void {
    $problems = [];

    // The home page is the brand link; the rest are pages under /dashboard, nested ones included
    // (a patient's map, a warehouse's medication, an import form).
    $pages = DashboardRoutes::all()
        ->filter(fn (RoutingRoute $route): bool => in_array('GET', $route->methods(), true) && str_starts_with($route->uri(), 'dashboard/'));

    foreach ($pages as $route) {
        $name = (string) $route->getName();
        $active = array_map(
            fn (NavItem $link): string => $link->label.' ('.$link->route.')',
            array_values(array_filter(Navigation::links(), fn (NavItem $link): bool => Str::is($link->active, $name))),
        );

        if (count($active) !== 1) {
            $problems[] = "{$name}: ".(count($active) === 0 ? 'no link' : implode(', ', $active));
        }
    }

    expect($pages)->not->toBeEmpty()
        ->and($problems)->toBe([]);
});

it('lists each page once', function (): void {
    $routes = array_map(fn (NavItem $link): ?string => $link->route, Navigation::links());

    expect($routes)->toBe(array_values(array_unique($routes)));
});

it('shows a group only with the links the account may open, and drops empty sections', function (): void {
    $visible = Navigation::visibleTo(fn (string $permission): bool => in_array($permission, ['list_regions', 'list_patients'], true));

    $shape = collect($visible)->map(fn ($section): array => [
        $section->title => collect($section->items)->map(fn (NavItem $item): array => [
            $item->label => array_map(fn (NavItem $link): string => (string) $link->route, $item->links()),
        ])->all(),
    ])->all();

    expect($shape)->toBe([
        ['Работа с пациентами' => [['Пациенты' => ['patients.index']]]],
        ['Организация' => [['Области' => ['regions.index']]]],
    ]);

    expect(Navigation::visibleTo(fn (): bool => false))->toBe([]);
});

it('keeps the sidebar to two levels', function (): void {
    expect(fn (): NavItem => NavItem::group('Склад', 'bx-archive', [
        NavItem::group('Вложенная', 'bx-box', [NavItem::link('Список', 'warehouses.index', 'list_warehouses')]),
    ]))->toThrow(InvalidArgumentException::class);
});
