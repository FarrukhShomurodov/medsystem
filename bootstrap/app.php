<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\RejectMalformedInput;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\ImplicitRouteBinding;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        health: '/up',
        // The dashboard's AJAX endpoints run on the admin session (and its CSRF token), not as a stateless API.
        then: function (): void {
            Route::middleware(['web', 'auth:admin'])
                ->prefix('api')
                ->name('api.')
                ->group(base_path('routes/ajax.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The dashboard login is the only sign-in page in the application.
        $middleware->redirectGuestsTo(fn (): string => route('dashboard.login'));

        $middleware->web(
            // First in the group, so the headers also reach the 401/403/419 and login-redirect
            // responses of the middleware after it (auth:admin is sorted ahead of appended ones).
            prepend: [SecurityHeaders::class, RejectMalformedInput::class],
            // Every session of the group is a dashboard one: a dismissed employee's ends here,
            // before the route's permission checks.
            append: [EnsureAccountIsActive::class],
        );

        // A changed password ends the account's other sessions on their next request (the session
        // keeps a MAC of the password hash; "remember me" cookies carry one already).
        $middleware->authenticateSessions();

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // AJAX endpoints answer with JSON (401, 403, 419, 422 ...) even for a plain fetch() without an Accept header.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $request->is('api/*') || $request->expectsJson(),
        );

        // Reference data that other records still point to is protected by ON DELETE RESTRICT.
        // Deleting it is a user mistake, not a crash: explain it instead of answering 500.
        $isRestrictedDelete = fn (Throwable $e): bool => $e instanceof QueryException
            && in_array((string) $e->getCode(), ['23001', '23503'], true)
            && request()->isMethod('DELETE');

        $exceptions->dontReportWhen($isRestrictedDelete);

        // An id in the URL that PostgreSQL cannot read as a bigint ("abc", twenty digits) names no
        // record. Implicit route binding hands it to the query as it is, and PostgreSQL refuses it
        // (22P02, 22003): answer 404, as for an id that does not exist, instead of a 500.
        $exceptions->map(function (QueryException $e): Throwable {
            $isMalformedRouteKey = in_array((string) $e->getCode(), ['22P02', '22003'], true)
                && collect($e->getTrace())->contains('class', ImplicitRouteBinding::class);

            return $isMalformedRouteKey ? new NotFoundHttpException(previous: $e) : $e;
        });

        $exceptions->render(function (QueryException $e, Request $request) use ($isRestrictedDelete) {
            if (! $isRestrictedDelete($e)) {
                return null;
            }

            $message = __('Запись нельзя удалить: она используется в других разделах.');

            return $request->is('api/*') || $request->expectsJson()
                ? response()->json(['message' => $message], 409)
                : back()->with('error', $message);
        });
    })->create();
