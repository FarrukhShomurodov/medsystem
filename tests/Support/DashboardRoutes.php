<?php

namespace Tests\Support;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionNamedType;

/**
 * The routes of the dashboard (its pages under /dashboard and their AJAX endpoints under /api)
 * and the permissions their middleware requires.
 */
final class DashboardRoutes
{
    /**
     * Routes that need a signed-in admin but no permission. The login form is outside the
     * dashboard prefix and open to guests.
     */
    public const array AUTHENTICATED_ONLY = [
        'logout' => 'every account must be able to sign out, whatever its permissions',
    ];

    /**
     * @return Collection<int, Route>
     */
    public static function all(): Collection
    {
        return collect(app(Router::class)->getRoutes()->getRoutes())
            ->filter(fn (Route $route): bool => preg_match('#^(dashboard|api)(/|$)#', $route->uri()) === 1)
            ->values();
    }

    /**
     * GET routes without URI parameters: the pages a test can open without creating records.
     *
     * @return Collection<int, Route>
     */
    public static function parameterlessPages(): Collection
    {
        return self::all()
            ->filter(fn (Route $route): bool => in_array('GET', $route->methods(), true) && $route->parameterNames() === [])
            ->values();
    }

    /**
     * One list per permission middleware of the route ("permission:a|b" gives ['a', 'b']).
     * A request passes when it holds one permission of every list.
     *
     * @return list<list<string>>
     */
    public static function permissionsOf(Route $route): array
    {
        return collect($route->gatherMiddleware())
            ->filter(fn (mixed $middleware): bool => is_string($middleware) && str_starts_with($middleware, 'permission:'))
            ->map(fn (string $middleware): array => explode('|', Str::of($middleware)->after('permission:')->before(',')->toString()))
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $granted
     */
    public static function allows(Route $route, array $granted): bool
    {
        foreach (self::permissionsOf($route) as $anyOf) {
            if (array_intersect($anyOf, $granted) === []) {
                return false;
            }
        }

        return true;
    }

    public static function label(Route $route): string
    {
        return implode('|', array_diff($route->methods(), ['HEAD'])).' /'.$route->uri();
    }

    /**
     * The URL of the route with a new factory-made record for every model-bound parameter, or null
     * when a parameter is not bound to a model with a factory (plain ids, enum-like segments).
     */
    public static function urlWithFactoryRecords(Route $route): ?string
    {
        return self::urlWithRecords($route, fn (string $class): ?Model => method_exists($class, 'factory')
            ? $class::factory()->create()
            : null);
    }

    /**
     * The URL of the route with the first existing record of every model-bound parameter, or null
     * when a parameter is not a model or its table is empty.
     */
    public static function urlWithFirstRecords(Route $route): ?string
    {
        return self::urlWithRecords($route, fn (string $class): ?Model => $class::query()
            ->orderBy((new $class)->getKeyName())
            ->first());
    }

    /**
     * @param  Closure(class-string<Model>): ?Model  $record
     */
    private static function urlWithRecords(Route $route, Closure $record): ?string
    {
        $uri = $route->uri();

        foreach ($route->signatureParameters(['subClass' => Model::class]) as $parameter) {
            if (! in_array($parameter->getName(), $route->parameterNames(), true)) {
                continue;
            }

            $type = $parameter->getType();
            $model = $type instanceof ReflectionNamedType ? $record($type->getName()) : null;

            if ($model === null) {
                return null;
            }

            $uri = str_replace('{'.$parameter->getName().'}', (string) $model->getRouteKey(), $uri);
        }

        return str_contains($uri, '{') ? null : '/'.$uri;
    }
}
