<?php

use App\Models\Admin;
use App\Support\Authorization\PermissionCatalog;
use Database\Seeders\PermissionSeeder;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Symfony\Component\Finder\SplFileInfo;
use Tests\Support\DashboardRoutes;

use function Pest\Laravel\seed;

/*
| Static checks over the route table, controller middleware included: they catch a new route
| (or controller action) that forgets its permission before any page test has to.
*/

it('requires the admin session and a permission on every dashboard route', function (): void {
    $routes = DashboardRoutes::all();

    $unguarded = $routes
        ->reject(fn (Route $route): bool => array_key_exists((string) $route->getName(), DashboardRoutes::AUTHENTICATED_ONLY))
        ->reject(function (Route $route): bool {
            $middleware = collect($route->gatherMiddleware());

            return $middleware->contains('auth:admin')
                && $middleware->contains(fn (mixed $name): bool => is_string($name) && preg_match('/^(permission|role|role_or_permission):/', $name) === 1);
        })
        ->map(DashboardRoutes::label(...))
        ->values()
        ->all();

    expect($routes)->not->toBeEmpty()
        ->and($unguarded)->toBe([]);
});

it('limits the routes without a permission to the documented sign-in actions', function (): void {
    foreach (array_keys(DashboardRoutes::AUTHENTICATED_ONLY) as $name) {
        $route = app(Router::class)->getRoutes()->getByName($name);

        expect($route)->not->toBeNull()
            ->and($route->gatherMiddleware())->toContain('auth:admin');
    }
});

it('names only seeded permissions in route and controller middleware', function (): void {
    // An unknown name does not fail loudly: the permission middleware just refuses everyone.
    seed(PermissionSeeder::class);

    $seeded = Permission::query()->where('guard_name', Admin::GUARD)->pluck('name');

    $unknown = DashboardRoutes::all()
        ->flatMap(fn (Route $route): array => array_merge([], ...DashboardRoutes::permissionsOf($route)))
        ->unique()
        ->diff($seeded)
        ->values()
        ->all();

    expect($unknown)->toBe([]);
});

it('seeds only permissions that a route, a view or the code checks', function (): void {
    // About sixty seeded permissions (view_* of pages without a show action, the laboratory
    // module's ...) were checked nowhere: granting them on the position form did nothing.
    $checks = '/(?:@can|@canany|@cannot|->can|->canAny|->cannot|->hasPermissionTo|->hasAnyPermission)\s*\(([^)]*)\)/';

    $checkedInCode = collect([...File::allFiles(app_path()), ...File::allFiles(resource_path('views'))])
        ->flatMap(function (SplFileInfo $file) use ($checks): array {
            preg_match_all($checks, $file->getContents(), $calls);
            preg_match_all("/'([a-z_]+)'/", implode(' ', $calls[1]), $names);

            return $names[1];
        });

    $checkedByRoutes = DashboardRoutes::all()
        ->flatMap(fn (Route $route): array => array_merge([], ...DashboardRoutes::permissionsOf($route)));

    expect(array_values(array_diff(PermissionCatalog::names(), [...$checkedInCode, ...$checkedByRoutes])))->toBe([]);
});

it('resolves every dashboard route middleware to a class', function (): void {
    // A permission name without the "permission:" prefix is taken for a class name and fails with a 500.
    app(HttpKernel::class); // registers the middleware aliases and groups with the router
    $router = app(Router::class);

    $unresolved = DashboardRoutes::all()
        ->flatMap(fn (Route $route) => collect($router->gatherRouteMiddleware($route))
            ->filter(fn (mixed $middleware): bool => is_string($middleware) && ! class_exists(Str::before($middleware, ':')))
            ->map(fn (string $middleware): string => DashboardRoutes::label($route).' -> '.$middleware))
        ->values()
        ->all();

    expect($unresolved)->toBe([]);
});

it('points every dashboard route at an existing controller method', function (): void {
    // Route::resource() registers all seven actions; the ones a controller lacks must be excluded.
    $missing = DashboardRoutes::all()
        ->filter(fn (Route $route): bool => $route->getControllerClass() !== null
            && ! method_exists($route->getControllerClass(), $route->getActionMethod()))
        ->map(fn (Route $route): string => DashboardRoutes::label($route).' -> '.$route->getActionName())
        ->values()
        ->all();

    expect($missing)->toBe([]);
});
