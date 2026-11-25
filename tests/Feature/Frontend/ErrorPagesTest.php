<?php

use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\Feature\Frontend\RenderedPage;

/*
| resources/views/errors: Russian pages for the HTTP errors the dashboard produces, on a standalone
| layout that needs neither the database nor a signed-in account, and that still renders when the
| front-end build is missing.
*/

beforeEach(function (): void {
    config(['app.debug' => false]);

    Route::middleware('web')->get('/_frontend-test/abort/{status}', fn (int $status) => abort($status));
    Route::middleware('web')->get('/_frontend-test/fail', fn (): never => throw new RuntimeException('Сбой для проверки'));
    Route::middleware('web')->get('/_frontend-test/forbidden', fn () => abort(403, request()->query('reason', '')));
});

dataset('error pages', [
    '403 forbidden' => [403, 'Доступ запрещён'],
    '404 not found' => [404, 'Страница не найдена'],
    '419 session expired' => [419, 'Сессия истекла'],
    '429 too many requests' => [429, 'Слишком много запросов'],
    '500 server error' => [500, 'Ошибка на сервере'],
    '503 maintenance' => [503, 'Идут технические работы'],
]);

it('answers an HTTP error with its Russian page and a way back to the dashboard', function (int $status, string $title): void {
    $response = $this->get("/_frontend-test/abort/{$status}")->assertStatus($status);

    $response->assertSeeHtml('<html lang="ru">')
        ->assertSeeHtml("<title>{$status} · {$title} · ".e(config('app.name')).'</title>')
        ->assertSeeText($title)
        ->assertSeeHtml('href="'.url('dashboard').'"');

    expect(RenderedPage::find($response->getContent(), RenderedPage::TEMPLATE_PATTERNS))->toBe([])
        ->and(RenderedPage::find($response->getContent(), RenderedPage::CDN_PATTERNS))->toBe([]);
})->with('error pages');

it('shows the page of a route that does not exist', function (): void {
    $this->get('/no-such-page-anywhere')->assertNotFound()->assertSeeText('Страница не найдена');
});

it('shows the page of an unexpected exception without its details', function (): void {
    $this->get('/_frontend-test/fail')
        ->assertStatus(500)
        ->assertSeeText('Ошибка на сервере')
        ->assertDontSeeText('Сбой для проверки');
});

it('gives the Russian reason of a refusal, but not the English message of a package', function (): void {
    $reason = 'Учётную запись главного администратора может изменить только главный администратор.';

    $this->get('/_frontend-test/forbidden?reason='.urlencode($reason))->assertForbidden()->assertSeeText($reason);

    $this->get('/_frontend-test/forbidden?reason='.urlencode('User does not have the right permissions.'))
        ->assertForbidden()
        ->assertDontSeeText('User does not have the right permissions.')
        ->assertSeeText('нет прав на эту страницу');
});

it('still renders the 500 page when the front-end build is missing', function (): void {
    // The real Vite, pointed at a build that does not exist: what a deploy without `npm run build` gives.
    $this->withVite();
    app(Vite::class)->useBuildDirectory('missing-build-'.Str::random(8));

    $this->get('/_frontend-test/fail')
        ->assertStatus(500)
        ->assertSeeText('Ошибка на сервере')
        ->assertDontSeeHtml('rel="stylesheet"');

    // A dashboard page cannot render without its build; the error page it falls into still can.
    $this->actingAs(adminWithPermissions('view_dashboard'), 'admin')
        ->get('/dashboard')
        ->assertStatus(500)
        ->assertSeeText('Ошибка на сервере');
});

/** Point every connection a request could open at a closed port, and the build at a missing directory. */
function breakDatabaseAndBuild(): string
{
    test()->withVite();
    app(Vite::class)->useBuildDirectory('missing-build-'.Str::random(8));

    $default = config('database.default');
    config([
        'database.connections.unreachable' => [...config("database.connections.{$default}"), 'host' => '127.0.0.1', 'port' => 1],
        'database.default' => 'unreachable',
    ]);

    return $default;
}

it('renders the error pages with neither a database nor a front-end build', function (int $status, string $title): void {
    // A layout that queried anything (the signed-in account, a setting) would fail inside the error page.
    $default = breakDatabaseAndBuild();

    try {
        $this->get("/_frontend-test/abort/{$status}")
            ->assertStatus($status)
            ->assertSeeText($title)
            ->assertSee(url('dashboard'), false);
    } finally {
        config(['database.default' => $default]);
    }
})->with('error pages');

it('renders the 500 page of a request whose database is down', function (): void {
    $default = breakDatabaseAndBuild();
    Route::middleware('web')->get('/_frontend-test/database', fn (): int => DB::table('admins')->count());

    try {
        $this->get('/_frontend-test/database')
            ->assertStatus(500)
            ->assertSeeText('Ошибка на сервере')
            ->assertDontSeeText('SQLSTATE');
    } finally {
        config(['database.default' => $default]);
    }
});
