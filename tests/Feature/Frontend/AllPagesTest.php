<?php

use App\Models\Admin;
use App\Models\Document;
use App\Models\DocumentResponse;
use App\Models\Patient;
use App\Models\ServiceType;
use App\Models\Staff;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\Feature\Frontend\RenderedPage;
use Tests\Support\DashboardRoutes;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

/*
| Every page of the dashboard, found through the router rather than listed by hand, rendered for
| the main administrator over the demo hospital. A new page is checked as soon as its route exists.
*/

/** Routes that answer with a file, not a page: they only have to answer. */
const ALL_PAGES_DOWNLOADS = ['cashier.export', 'registrar.export', 'patients.files.show'];

/**
 * The verb a form page's heading starts with, by the last part of its route name: the same words as
 * the "Создать" and "Изменить" buttons of the lists (docs/architecture/frontend.md).
 */
const FORM_PAGE_VERBS = ['create' => 'Создать', 'edit' => 'Изменить'];

/** Form pages named by what they do in the domain rather than by the verb of the list button. */
const FORM_PAGES_WITH_OWN_VERB = ['patient.services.create' => 'an order is "assigned" to a patient'];

/** Routes the demo hospital has no record for: they are opened by their own tests. */
const ALL_PAGES_WITHOUT_DEMO_RECORD = [
    'patients.files.show' => 'the demo patients have no uploaded files (tests/Feature/Patients covers it)',
];

/**
 * @return array<string, string|null> route name => URL (null: no demo record to open it with)
 */
function allDashboardPages(): array
{
    $doctor = Staff::query()
        ->whereRelation('professionalData', 'admission_period', '>', 0)
        ->whereHas('services', fn (Builder $service) => $service->where('is_appointment', true))
        ->firstOrFail();

    // The first patient has an open stay: a second map cannot be started.
    $outpatient = Patient::query()->whereDoesntHave('examinationMaps', fn (Builder $map) => $map->where('is_active', true))->orderBy('id')->firstOrFail();

    // Pages whose first record would only redirect or be refused, or whose parameters are plain
    // ids or a query.
    $special = [
        'receptions.doctor.calendar' => route('receptions.doctor.calendar', [$doctor, $doctor->services()->where('is_appointment', true)->value('services.id')]),
        'patient.map.create' => route('patient.map.create', $outpatient),
        // The answers page opens only an initial nursing assessment a stay form links, which the
        // demo does not fill in: this one is linked by the new-stay form above.
        'documents.show.answer' => route('documents.show.answer', DocumentResponse::factory()->create([
            'patient_id' => $outpatient->id,
            'document_id' => Document::INITIAL_NURSING_ASSESSMENT,
        ])),
        'documents.show' => route('documents.show', [Document::query()->orderBy('id')->firstOrFail(), 'patient_id' => Patient::query()->orderBy('id')->value('id')]),
        // The seeded service types are read-only.
        'service.types.edit' => route('service.types.edit', ServiceType::factory()->create(['id' => ServiceType::LAST_SYSTEM_TYPE_ID + 1])),
    ];

    return DashboardRoutes::all()
        ->filter(fn (Route $route): bool => in_array('GET', $route->methods(), true) && str_starts_with($route->uri(), 'dashboard'))
        ->mapWithKeys(fn (Route $route): array => [
            (string) $route->getName() => $special[$route->getName()] ?? DashboardRoutes::urlWithFirstRecords($route),
        ])
        ->all();
}

it('renders every dashboard page over the demo data with sound, accessible markup', function (): void {
    seed(DatabaseSeeder::class);
    seed(DemoSeeder::class);
    actingAs(Admin::query()->where('email', 'admin@medsystem.test')->firstOrFail(), 'admin');

    $pages = allDashboardPages();
    $problems = [];
    $rendered = [];

    expect(count($pages))->toBeGreaterThan(100);

    foreach ($pages as $name => $url) {
        if ($url === null) {
            if (! array_key_exists($name, ALL_PAGES_WITHOUT_DEMO_RECORD)) {
                $problems[] = "{$name}: no demo record to open it with";
            }

            continue;
        }

        $response = get($url);
        $status = $response->getStatusCode();

        if ($status >= 400) {
            $problems[] = "{$name} ({$url}): status {$status}";

            continue;
        }

        if (in_array($name, ALL_PAGES_DOWNLOADS, true)) {
            continue;
        }

        if ($status !== 200) {
            $problems[] = "{$name} ({$url}): status {$status}, to ".$response->headers->get('Location');

            continue;
        }

        $html = (string) $response->getContent();

        $found = [
            ...array_map(fn (string $pattern): string => "refers to {$pattern}", RenderedPage::find($html, RenderedPage::TEMPLATE_PATTERNS)),
            ...array_map(fn (string $pattern): string => "loads from {$pattern}", RenderedPage::find($html, RenderedPage::CDN_PATTERNS)),
            ...RenderedPage::markupProblems($html),
        ];

        if (substr_count($html, '<title>') !== 1) {
            $found[] = substr_count($html, '<title>').' <title> elements';
        }

        if (preg_match_all('/<h1\b/i', $html) !== 1) {
            $found[] = preg_match_all('/<h1\b/i', $html).' <h1> elements';
        }

        $found = [...$found, ...formPageNamingProblems($name, $html)];

        foreach (RenderedPage::rawValues($html) as $value) {
            $found[] = "prints the raw value {$value}";
        }

        foreach ($found as $problem) {
            $problems[] = "{$name}: {$problem}";
        }

        $rendered[$name] = $html;
    }

    expect($problems)->toBe([]);

    if (! RenderedPage::nodeAvailable()) {
        $this->markTestIncomplete('Node is not installed: the inline scripts were not parsed.');
    }

    expect(RenderedPage::unparsableScripts($rendered))->toBe([]);
});

/**
 * A create or edit page says what it does with the list button's verb, and its <title> starts with
 * its heading (the record, if any, follows in the title and the breadcrumbs).
 *
 * @return list<string>
 */
function formPageNamingProblems(string $route, string $html): array
{
    $verb = FORM_PAGE_VERBS[Str::afterLast($route, '.')] ?? null;

    if ($verb === null || array_key_exists($route, FORM_PAGES_WITH_OWN_VERB)) {
        return [];
    }

    $text = fn (string $pattern): string => preg_match($pattern, $html, $match) === 1
        ? trim(html_entity_decode(strip_tags($match[1]), ENT_QUOTES | ENT_HTML5))
        : '';
    $heading = $text('#<h1\b[^>]*>(.*?)</h1>#s');
    $title = $text('#<title>(.*?)</title>#s');
    $problems = [];

    if (! str_starts_with($heading, $verb.' ')) {
        $problems[] = "heading \"{$heading}\" does not start with \"{$verb}\"";
    }

    if (! str_starts_with($title, $heading)) {
        $problems[] = "<title> \"{$title}\" does not start with the heading \"{$heading}\"";
    }

    return $problems;
}

it('prints no unescaped value in any view', function (): void {
    // @lang and @choice echo the translation unescaped too; {{ __() }} and {{ trans_choice() }} escape it.
    $raw = collect(File::allFiles(resource_path('views')))
        ->filter(fn (SplFileInfo $file): bool => str_ends_with($file->getFilename(), '.blade.php'))
        ->filter(fn (SplFileInfo $file): bool => preg_match('/\{!!|@lang\(|@choice\(/', (string) file_get_contents($file->getPathname())) === 1)
        ->map(fn (SplFileInfo $file): string => str_replace('\\', '/', $file->getRelativePathname()))
        ->values()
        ->all();

    expect($raw)->toBe([]);
});

it('renders every page a demo account may open', function (string $email): void {
    seed(DatabaseSeeder::class);
    seed(DemoSeeder::class);

    $account = Admin::query()->where('email', $email)->firstOrFail();
    $granted = $account->getAllPermissions()->pluck('name')->all();
    actingAs($account, 'admin');

    $routes = DashboardRoutes::all()->keyBy(fn (Route $route): string => (string) $route->getName());
    $problems = [];
    $opened = 0;

    foreach (allDashboardPages() as $name => $url) {
        if ($url === null || in_array($name, ALL_PAGES_DOWNLOADS, true) || ! DashboardRoutes::allows($routes[$name], $granted)) {
            continue;
        }

        $response = get($url);
        $opened++;

        if ($response->getStatusCode() !== 200) {
            $problems[] = "{$name}: status {$response->getStatusCode()}";

            continue;
        }

        foreach (RenderedPage::markupProblems((string) $response->getContent()) as $problem) {
            $problems[] = "{$name}: {$problem}";
        }
    }

    expect($opened)->toBeGreaterThan(0)
        ->and($problems)->toBe([]);
})->with(['registrar@medsystem.test', 'cashier@medsystem.test']);

it('catches values printed raw, and leaves form values, scripts and formatted values alone', function (): void {
    $html = '<html><body><table><tr><td>2026-11-18</td><td>2026-11-18 19:06:26</td><td>51250.00</td><td>41000.0000</td>'
        .'<td>18.11.2026</td><td>18.11.2026 19:06</td><td>150&nbsp;000,00</td><td>Натрия хлорид 0.9%</td></tr></table>'
        .'<input type="date" name="day" value="2026-11-18"><textarea name="note">2026-11-17</textarea>'
        .'<script>const day = "2026-11-16";</script><template><td>2026-11-15</td></template></body></html>';

    expect(RenderedPage::rawValues($html))->toBe(['2026-11-18', '2026-11-18 19:06:26', '51250.00', '41000.0000']);
});

it('catches each markup problem it looks for', function (): void {
    $html = '<!DOCTYPE html><html><body>'
        .'<div class="form-group"><i class="fa fa-plus"></i><span class="bg-label-success">1</span></div>'
        .'<button type="button" onclick="go()">Да</button><a href="javascript:void(0)">Ссылка</a><a href="#" data-toggle="modal">Окно</a>'
        .'<img src="/logo.png"><input type="text" name="search"><button type="button"><i class="bx bx-x"></i></button>'
        .'<a href="/next" aria-label=">"><i class="bx bx-chevron-right"></i></a><h1>Страница</h1><h4>Раздел</h4>'
        .'<form method="POST" action="/save"><button>Сохранить</button></form>'
        .'<form method="put"></form><button type="submit" form="missing">Отправить</button>'
        .'<span id="twice"></span><span id="twice"></span>'
        .'<select name="gender" aria-label="Пол"><option value="" disabled>Выберите пол</option><option value="male">Мужской</option></select>'
        // Fine: a labelled field, a named icon button, a wizard button, a stepper target, a template row.
        .'<form method="post"><input type="hidden" name="_token" value="t"><label for="name">Имя</label><input id="name" name="name">'
        .'<button type="button" aria-label="Закрыть"><i class="bx bx-x"></i></button><button class="btn btn-next">Далее</button></form>'
        .'<div class="step" data-target="#pane"></div><template><input name="row[__INDEX__]"><span id="twice"></span></template>'
        .'<select name="region" aria-label="Область"><option value="" disabled selected>Выберите область</option><option value="1">Ташкент</option></select>'
        .'</body></html>';

    expect(RenderedPage::markupProblems($html))->toBe([
        'old class "form-group" on <div class="form-group">',
        'old class "fa" on <i class="fa fa-plus">',
        'old class "fa-plus" on <i class="fa fa-plus">',
        'old class "bg-label-success" on <span class="bg-label-success">',
        'inline handler onclick= on <button type="button">',
        'javascript: URL on <a href="javascript:void(0)">',
        'Bootstrap 4 attribute data-toggle= on <a href="#">',
        'image without alt: <img>',
        'form control without a label: <input name="search" type="text">',
        'button or link without a name: <button type="button">',
        'button or link without a name: <a href="/next">',
        'POST form without its CSRF token: <form>',
        'form method="put" (use @method): <form>',
        'button without a type inside a form: <button>',
        'placeholder never selected: <select name="gender">',
        'form="missing" names no form: <button type="submit">',
        'id "twice" used 2 times',
        'heading skips from h1 to h4: "Раздел"',
    ]);
});
