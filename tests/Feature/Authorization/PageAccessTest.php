<?php

use App\Models\Admin;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Routing\Route;
use Tests\Support\DashboardRoutes;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

/*
| Every page and AJAX GET endpoint of the dashboard, opened by accounts with different permissions.
| Routes with URI parameters are opened with records where every parameter is a bound model.
| Pages that must open are rendered on the reference data of a fresh install (DatabaseSeeder);
| refusals need only the permissions. Frontend/AllPagesTest renders every page over the demo
| hospital, so the demo data is seeded here only for the AJAX endpoints.
*/

/**
 * Status of every parameterless page that differs from what the callback expects for it.
 *
 * @param  Closure(Route): int  $expectedStatus
 * @return list<string>
 */
function pagesAnsweringOtherThan(Closure $expectedStatus): array
{
    return DashboardRoutes::parameterlessPages()
        ->map(function (Route $route) use ($expectedStatus): ?string {
            $expected = $expectedStatus($route);
            $actual = get('/'.$route->uri())->getStatusCode();

            return $actual === $expected ? null : DashboardRoutes::label($route)." answered {$actual}, expected {$expected}";
        })
        ->filter()
        ->values()
        ->all();
}

/**
 * GET routes with URI parameters, keyed by route name, with the URL the callback builds for them.
 *
 * @param  Closure(Route): ?string  $url
 * @return array<string, string>
 */
function recordPageUrls(Closure $url): array
{
    return DashboardRoutes::all()
        ->filter(fn (Route $route): bool => in_array('GET', $route->methods(), true)
            && $route->parameterNames() !== []
            && ! $route->enforcesScopedBindings())
        ->mapWithKeys(fn (Route $route): array => [$route->getName() => $url($route)])
        ->filter()
        ->all();
}

it('forbids every dashboard page to an admin without a role', function (): void {
    seed(PermissionSeeder::class);
    actingAs(Admin::factory()->create(), 'admin');

    expect(pagesAnsweringOtherThan(fn (): int => 403))->toBe([]);
});

it('forbids the record pages to an admin without a role', function (): void {
    seed(PermissionSeeder::class);
    actingAs(Admin::factory()->create(), 'admin');

    $urls = recordPageUrls(DashboardRoutes::urlWithFactoryRecords(...));
    $opened = array_map(fn (string $url): int => get($url)->getStatusCode(), $urls);

    // The rest take plain ids or several records; the route checks cover their middleware.
    expect(count($urls))->toBeGreaterThan(40)
        ->and(array_filter($opened, fn (int $status): bool => $status !== 403))->toBe([]);
});

it('opens every dashboard page for main_admin', function (): void {
    seed(DatabaseSeeder::class);
    actingAs(Admin::factory()->mainAdmin()->create(), 'admin');

    expect(pagesAnsweringOtherThan(fn (): int => 200))->toBe([]);
});

it('answers every AJAX GET endpoint for main_admin over the demo hospital', function (): void {
    seed([DatabaseSeeder::class, DemoSeeder::class]);
    actingAs(Admin::factory()->mainAdmin()->create(), 'admin');

    $urls = DashboardRoutes::all()
        ->filter(fn (Route $route): bool => str_starts_with($route->uri(), 'api/')
            && in_array('GET', $route->methods(), true)
            && ! $route->enforcesScopedBindings())
        ->mapWithKeys(fn (Route $route): array => [(string) $route->getName() => DashboardRoutes::urlWithFirstRecords($route)])
        ->filter()
        ->all();

    $failed = collect($urls)
        ->map(fn (string $url): int => get($url)->getStatusCode())
        ->reject(fn (int $status): bool => $status === 200)
        ->all();

    // Endpoints whose parameters are plain ids get no URL; the endpoint tests of their module cover them.
    expect(count($urls))->toBeGreaterThan(10)
        ->and($failed)->toBe([]);
});

dataset('staff roles with default permissions', ['Регистратор', 'Кассир']);

it('opens exactly the pages its permissions allow for a staff role', function (string $role): void {
    seed(DatabaseSeeder::class);
    $admin = Admin::factory()->role($role)->create();
    $granted = $admin->getAllPermissions()->pluck('name')->all();

    actingAs($admin, 'admin');

    expect(pagesAnsweringOtherThan(fn (Route $route): int => DashboardRoutes::allows($route, $granted) ? 200 : 403))
        ->toBe([]);
})->with('staff roles with default permissions');
