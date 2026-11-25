<?php

use App\Models\Admin;
use App\Models\ExaminationMap;
use App\Models\Patient;
use App\Models\Reception;
use App\Queries\Dashboard\DashboardCard;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Database\Seeders\WarehouseSeeder;
use Illuminate\Routing\Router;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Frontend\RenderedPage;
use Tests\Support\DashboardRoutes;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

/*
| The cards of the dashboard home (App\Queries\Dashboard\DashboardCard): each links to one page and is
| shown to exactly the accounts that may open it, like the entries of the sidebar.
*/

/**
 * The data-dashboard-card values of a rendered dashboard, in page order.
 *
 * @return list<string>
 */
function dashboardCards(TestResponse $response): array
{
    $document = new DOMDocument;
    $document->loadHTML('<?xml encoding="utf-8"?>'.$response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);

    $cards = (new DOMXPath($document))->query('//main//*[@data-dashboard-card]/@data-dashboard-card');

    return array_values(array_map(fn (DOMAttr $card): string => $card->value, iterator_to_array($cards)));
}

/**
 * The links of a card: the card itself when it is a link, and the links inside it.
 *
 * @return list<string>
 */
function cardLinks(TestResponse $response, DashboardCard $card): array
{
    $document = new DOMDocument;
    $document->loadHTML('<?xml encoding="utf-8"?>'.$response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);

    $hrefs = (new DOMXPath($document))->query('//main//*[@data-dashboard-card="'.$card->value.'"]/descendant-or-self::a/@href');

    return array_values(array_map(fn (DOMAttr $href): string => $href->value, iterator_to_array($hrefs)));
}

/**
 * Every permission a card needs, the dashboard's own included.
 *
 * @return list<string>
 */
function allCardPermissions(): array
{
    return array_values(array_unique(array_merge(['view_dashboard'], ...array_map(
        fn (DashboardCard $card): array => $card->permissions(),
        DashboardCard::cases(),
    ))));
}

dataset('dashboard cards', fn (): array => array_combine(
    array_map(fn (DashboardCard $card): string => $card->value, DashboardCard::cases()),
    array_map(fn (DashboardCard $card): array => [$card], DashboardCard::cases()),
));

beforeEach(function (): void {
    seed(WarehouseSeeder::class);
});

it('links every card to a dashboard page whose middleware checks the card\'s first permission', function (DashboardCard $card): void {
    $route = app(Router::class)->getRoutes()->getByName($card->route());

    expect($route)->not->toBeNull()
        ->and($route->methods())->toContain('GET')
        ->and(DashboardRoutes::permissionsOf($route))->toBe([[$card->permissions()[0]]]);
})->with('dashboard cards');

it('shows a card to an account with its permissions, linking to its page', function (DashboardCard $card): void {
    // A patient in hospital with an appointment today, for the cards that link to patients.
    $patient = Patient::factory()->create();
    ExaminationMap::factory()->for($patient)->create();
    Reception::factory()->for($patient)->create();

    $response = actingAs(adminWithPermissions('view_dashboard', ...$card->permissions()), 'admin')
        ->get(route('dashboard'))
        ->assertOk();

    $page = $card->route() === 'patients.show' ? route('patients.show', $patient) : route($card->route());
    $links = array_filter(cardLinks($response, $card), fn (string $href): bool => str_starts_with($href, $page));

    expect(dashboardCards($response))->toContain($card->value)
        ->not->toContain('welcome')
        ->and($links)->not->toBeEmpty();
})->with('dashboard cards');

it('hides a card from an account that lacks one of its permissions, whatever else it holds', function (DashboardCard $card): void {
    foreach ($card->permissions() as $missing) {
        $granted = array_values(array_diff(allCardPermissions(), [$missing]));

        $response = actingAs(adminWithPermissions(...$granted), 'admin')->get(route('dashboard'))->assertOk();

        expect(dashboardCards($response))->not->toContain($card->value);
    }
})->with('dashboard cards');

it('shows every card, in page order, to an account holding all their permissions', function (): void {
    $response = actingAs(adminWithPermissions(...allCardPermissions()), 'admin')->get(route('dashboard'))->assertOk();

    expect(dashboardCards($response))->toBe(array_map(fn (DashboardCard $card): string => $card->value, DashboardCard::cases()))
        ->and(RenderedPage::markupProblems((string) $response->getContent()))->toBe([]);
});

it('welcomes an account that may only open the dashboard, with no card and no action', function (): void {
    $response = actingAs(adminWithPermissions('view_dashboard'), 'admin')
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Добро пожаловать')
        ->assertDontSee(route('patients.create'))
        ->assertDontSee(route('receptions.appointment'));

    expect(dashboardCards($response))->toBe(['welcome']);
});

it('offers the quick actions to the accounts that may take them', function (): void {
    actingAs(adminWithPermissions('view_dashboard', 'create_patients'), 'admin')
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeHtml('href="'.route('patients.create').'"')
        ->assertDontSee(route('receptions.appointment'));

    actingAs(adminWithPermissions('view_dashboard', 'view_appointment_receptions'), 'admin')
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeHtml('href="'.route('receptions.appointment').'"')
        ->assertDontSee(route('patients.create'));
});

it('shows each demo role the cards of its permissions over the demo hospital', function (string $email, array $expected): void {
    seed([DatabaseSeeder::class, DemoSeeder::class]);
    $account = Admin::query()->where('email', $email)->firstOrFail();

    $response = actingAs($account, 'admin')->get(route('dashboard'))->assertOk();

    expect(dashboardCards($response))->toBe($expected)
        ->and(RenderedPage::markupProblems((string) $response->getContent()))->toBe([]);
})->with([
    'main administrator' => ['admin@medsystem.test', array_map(fn (DashboardCard $card): string => $card->value, DashboardCard::cases())],
    'registrar' => ['registrar@medsystem.test', ['awaiting-payment', 'paid-today', 'admissions', 'registrar-queue']],
    'cashier' => ['cashier@medsystem.test', ['awaiting-payment', 'paid-today']],
]);

it('opens the appointments of the day from the cards that count them', function (DashboardCard $card): void {
    // Both cards count today's appointments, but linked to the list of every day's.
    Reception::factory()->create();

    $response = actingAs(adminWithPermissions('view_dashboard', ...$card->permissions()), 'admin')
        ->get(route('dashboard'))
        ->assertOk();

    expect(cardLinks($response, $card))->toContain(route('receptions.index', ['date' => today()->toDateString()]))
        ->not->toContain(route('receptions.index'));
})->with([DashboardCard::ReceptionsToday, DashboardCard::Appointments]);
