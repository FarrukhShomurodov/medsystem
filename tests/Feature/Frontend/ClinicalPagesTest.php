<?php

use App\Models\Admin;
use App\Models\Procedure;
use App\Models\Region;
use App\Models\ServiceType;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Routing\Router;
use Tests\Feature\Frontend\RenderedPage;
use Tests\Support\DashboardRoutes;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

/*
| The pages of the clinical catalogues (services, diagnoses, medical documents, departments,
| equipment, regions ...) and the cashier, migrated to Tabler: rendered for the main administrator
| over the demo hospital. Pages with parameters open their first demo record.
*/

const CLINICAL_PAGES = [
    'regions.index', 'regions.create', 'regions.edit',
    'cities.index', 'cities.edit',
    'allegories.index', 'diseases.index',
    'floors.index', 'floors.show',
    'offices.index', 'offices.edit',
    'departments.index', 'departments.create', 'departments.edit',
    'departments.room.index', 'departments.room.create', 'departments.room.edit',
    'equipment.index', 'equipment.create', 'equipment.types.index',
    'services.index', 'services.create', 'services.edit', 'services.import.show',
    'patient.services.index', 'patient.services.create', 'patient.services.edit',
    'service.types.index', 'service.groups.index', 'service.groups.create', 'service.groups.edit',
    'procedures.index', 'procedures.edit', 'service.methods.index',
    'diagnoses.index', 'diagnoses.edit', 'diagnoses.import.show',
    'surveys.index', 'surveys.show', 'surveys.create', 'surveys.edit',
    'cashier.index',
];

/** Markup the migration removed: the old template's classes, inline styles and handlers. */
const CLINICAL_LEFTOVERS = [
    'input-group-merge', 'icon-border', 'breadcrumb-wrapper', 'card-datatable', 'datatables-users',
    'btn-label-', 'bg-label-', 'switch-toggle-slider', 'errors_alert', 'basic-icon-default',
];

it('renders the clinical pages without the old template, inline styles or handlers', function (): void {
    seed(DatabaseSeeder::class);
    seed(DemoSeeder::class);
    actingAs(Admin::query()->where('email', 'admin@medsystem.test')->firstOrFail(), 'admin');

    $problems = [];
    $rendered = [];

    foreach (CLINICAL_PAGES as $name) {
        $route = app(Router::class)->getRoutes()->getByName($name);
        $url = $route === null ? null : DashboardRoutes::urlWithFirstRecords($route);

        if ($url === null) {
            $problems[] = "{$name}: no route, or no demo record to open it with";

            continue;
        }

        $response = get($url);
        $html = (string) $response->getContent();

        if ($response->getStatusCode() !== 200) {
            $problems[] = "{$name} ({$url}): status {$response->getStatusCode()}";

            continue;
        }

        $found = [
            ...RenderedPage::find($html, RenderedPage::TEMPLATE_PATTERNS),
            ...RenderedPage::find($html, RenderedPage::CDN_PATTERNS),
            ...RenderedPage::find($html, CLINICAL_LEFTOVERS),
        ];

        // The layout's own markup has neither; the pages of this group must not add them.
        if (preg_match('/\sstyle\s*=/i', $html) === 1) {
            $found[] = 'style attribute';
        }

        if (preg_match('/<[^>]+\son[a-z]+\s*=/i', $html) === 1) {
            $found[] = 'inline event handler';
        }

        foreach ($found as $pattern) {
            $problems[] = "{$name}: {$pattern}";
        }

        $rendered[$name] = $html;
    }

    expect($problems)->toBe([]);

    if (! RenderedPage::nodeAvailable()) {
        $this->markTestIncomplete('Node is not installed: the inline scripts were not parsed.');
    }

    expect(RenderedPage::unparsableScripts($rendered))->toBe([]);
});

it('offers the row actions and the create button only to accounts that may use them', function (): void {
    $region = Region::factory()->create();

    actingAs(adminWithPermissions('list_regions'), 'admin')
        ->get(route('regions.index'))
        ->assertOk()
        ->assertSee($region->name)
        ->assertDontSee(route('regions.create'))
        ->assertDontSee(route('regions.edit', $region))
        ->assertDontSee(route('regions.destroy', $region));

    actingAs(adminWithPermissions('list_regions', 'create_regions', 'edit_regions', 'delete_regions'), 'admin')
        ->get(route('regions.index'))
        ->assertOk()
        ->assertSee(route('regions.create'))
        ->assertSee(route('regions.edit', $region))
        ->assertSeeHtml('action="'.route('regions.destroy', $region).'"')
        ->assertSeeHtml('data-confirm=');
});

it('keeps the seeded service types read-only in the list', function (): void {
    $seeded = ServiceType::factory()->create(['id' => ServiceType::LAST_SYSTEM_TYPE_ID]);
    $added = ServiceType::factory()->create(['id' => ServiceType::LAST_SYSTEM_TYPE_ID + 1]);

    actingAs(adminWithPermissions('list_service_types', 'edit_service_types', 'delete_service_types'), 'admin')
        ->get(route('service.types.index'))
        ->assertOk()
        ->assertDontSee(route('service.types.edit', $seeded))
        ->assertSee(route('service.types.edit', $added));
});

it('keeps the tiyins of a procedure price on its edit form', function (): void {
    // The form used to show round($procedure->price), so saving it changed 15000.50 into 15001.
    $procedure = Procedure::factory()->create(['price' => '15000.50']);

    actingAs(adminWithPermissions('edit_service_procedures'), 'admin')
        ->get(route('procedures.edit', $procedure))
        ->assertOk()
        ->assertSeeHtml('value="15000.50"')
        ->assertSeeHtml('step="0.01"');
});

it('posts the switches of the service form as 0 or 1 without a script', function (): void {
    actingAs(adminWithPermissions('create_services'), 'admin')
        ->get(route('services.create'))
        ->assertOk()
        ->assertSeeHtml('<input type="hidden" name="is_active" value="0">')
        ->assertSeeHtml('<input type="hidden" name="is_appointment" value="0">')
        ->assertSeeHtml('name="is_active" value="1"')
        ->assertSeeHtml('name="is_appointment" value="1"');
});

it('gives the page scripts their templates and endpoints through the markup', function (): void {
    actingAs(adminWithPermissions('create_surveys', 'create_patient_services', 'create_department_rooms'), 'admin');

    get(route('surveys.create'))
        ->assertOk()
        ->assertSeeHtml('data-survey-template="question"')
        ->assertSeeHtml('name="__QUESTION__[question]"')
        ->assertSeeHtml('data-survey-template="group-pane"')
        ->assertSeeHtml('name="groups[__GROUP__][title]"');

    get(route('patient.services.create'))
        ->assertOk()
        ->assertSeeHtml('data-maps-url="'.route('api.examination-maps.by-patient', ['patient' => '__PATIENT__']).'"');

    get(route('departments.room.create'))
        ->assertOk()
        ->assertSeeHtml('data-row-list-template')
        ->assertSeeHtml('name="bed_numbers[]"');
});
