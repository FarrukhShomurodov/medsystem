<?php

use App\Models\Admin;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Testing\TestResponse;
use Pest\Expectation;
use Tests\Support\DashboardRoutes;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

/*
| The sidebar (App\Support\Navigation\Navigation, drawn by <x-sidebar />) must gate each entry with the
| permission of the page it opens: an entry visible to an account that gets 403, or hidden from one
| that may open the page, is a bug.
*/

beforeEach(function (): void {
    seed(DatabaseSeeder::class);
});

/**
 * Paths of the page links in the sidebar of a rendered dashboard page.
 *
 * @return list<string>
 */
function sidebarLinks(TestResponse $response): array
{
    $document = new DOMDocument;
    $document->loadHTML((string) $response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);

    $hrefs = (new DOMXPath($document))
        ->query('//aside[@id="sidebar"]//ul[@id="sidebar-nav"]//a[contains(@class, "nav-link") or contains(@class, "dropdown-item")]/@href');

    return collect(iterator_to_array($hrefs))
        ->map(fn (DOMAttr $href): string => (string) parse_url($href->value, PHP_URL_PATH))
        ->filter(fn (string $path): bool => str_starts_with($path, '/dashboard'))
        ->values()
        ->all();
}

function routeOfPath(string $path): Route
{
    return app(Router::class)->getRoutes()->match(Request::create($path));
}

it('shows no menu entry to an account that may only open the dashboard', function (): void {
    actingAs(Admin::factory()->create()->givePermissionTo('view_dashboard'), 'admin');

    expect(sidebarLinks(get('/dashboard')->assertOk()))->toBe([]);
});

it('shows each menu entry to exactly the accounts that may open its page', function (): void {
    actingAs(Admin::factory()->mainAdmin()->create(), 'admin');
    $allLinks = sidebarLinks(get('/dashboard')->assertOk());

    expect(count($allLinks))->toBeGreaterThan(30);

    $pagePermissions = collect($allLinks)
        ->mapWithKeys(fn (string $path): array => [$path => DashboardRoutes::permissionsOf(routeOfPath($path))])
        ->each(fn (array $required, string $path): Expectation => expect($required)->toHaveCount(1, "{$path} has several permission checks"))
        ->map(fn (array $required): array => $required[0]);

    $mismatches = [];

    foreach ($pagePermissions->flatten()->unique() as $permission) {
        $granted = ['view_dashboard', $permission];
        actingAs(Admin::factory()->create()->givePermissionTo($granted), 'admin');
        $visible = sidebarLinks(get('/dashboard'));

        foreach ($visible as $path) {
            if (! DashboardRoutes::allows(routeOfPath($path), $granted)) {
                $mismatches[] = "{$permission} sees {$path}, which it may not open";
            }
        }

        foreach ($pagePermissions->filter(fn (array $anyOf): bool => in_array($permission, $anyOf, true))->keys() as $path) {
            if (! in_array($path, $visible, true)) {
                $mismatches[] = "{$permission} may open {$path} but the menu hides it";
            } elseif (($status = get($path)->getStatusCode()) !== 200) {
                $mismatches[] = "{$permission} opens {$path} from the menu and gets {$status}";
            }
        }
    }

    expect($mismatches)->toBe([]);
});
