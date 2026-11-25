<?php

use App\Models\Admin;
use App\Models\HouseholdMaterial;
use App\Models\Manufacturer;
use App\Models\MedicalDevice;
use App\Models\MedicalSupply;
use App\Models\Medication;
use App\Models\MedicationGroup;
use App\Models\Pharmacy;
use App\Models\PharmacyType;
use App\Models\Supplier;
use App\Models\Transfer;
use App\Models\Warehouse;
use App\Models\WarehouseType;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Database\Seeders\WarehouseSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Router;
use Tests\Feature\Frontend\RenderedPage;
use Tests\Feature\Stock\StockFixtures;
use Tests\Support\DashboardRoutes;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

/*
| The stock screens (main and department warehouses, pharmacies, transfers, medications and their
| reference lists) after the move to Tabler: every page opens over the demo hospital without the
| old template, its inline scripts parse, and the controls follow the permissions.
*/

/** Every page of the stock folders; pages with parameters open their first demo record. */
const STOCK_PAGES = [
    'main.warehouse.remains', 'main.warehouse.show.medication.remain', 'main.warehouse.receipts.goods.index', 'supply-requests.index',
    'warehouses.index', 'warehouses.create', 'warehouses.edit', 'warehouses.show', 'warehouses.medications',
    'warehouse.medication.show', 'warehouses.supplies', 'warehouse.remains', 'warehouse.order',
    'warehouses.type.index', 'warehouses.type.create', 'warehouses.type.edit',
    'pharmacies.index', 'pharmacies.create', 'pharmacies.edit', 'pharmacy.medications',
    'pharmacy.types.index', 'pharmacy.types.create', 'pharmacy.types.edit',
    'transfers.index', 'transfers.create', 'transfers.edit', 'transfer.medications',
    'medications.index', 'medications.create', 'medications.edit', 'medications.show', 'dangerous.combination.import.show',
    'medication.groups.index', 'medication.groups.create', 'medication.groups.edit',
    'supplies.index', 'supplies.create', 'supplies.edit',
    'household.materials.index', 'household.materials.create', 'household.materials.edit',
    'manufacturers.index', 'manufacturers.create', 'manufacturers.edit',
    'suppliers.index', 'suppliers.create', 'suppliers.edit',
    'devices.index', 'devices.create', 'devices.edit',
];

/** Class names and markup of the removed template that these pages must not use any more. */
const STOCK_OLD_MARKUP = [
    'input-group-merge', 'icon-border', 'breadcrumb-wrapper', 'card-datatable', 'datatables-users', 'btn-label-',
    'bg-label-', 'alert-solid-', 'switch-input', 'btn-close-white', 'avatar-initial', 'form-group', 'errors_alert',
    'basic-icon-default', 'class="fas ', 'class="fa ', 'class="bi ', 'MED ERA', '<style',
];

/** What the page scripts must leave to app.js: its widget initialisers and dialogs. */
const STOCK_SCRIPT_LEFTOVERS = ['.select2(', 'flatpickr(', 'new Stepper', 'window.alert', 'window.confirm', ' alert(', ' confirm(', '!confirm(', "'/api/", "'/dashboard/"];

it('renders every stock page over the demo data without the old template', function (): void {
    seed(DatabaseSeeder::class);
    seed(DemoSeeder::class);
    actingAs(Admin::query()->where('email', 'admin@medsystem.test')->firstOrFail(), 'admin');

    $problems = [];
    $rendered = [];

    foreach (STOCK_PAGES as $name) {
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
            ...RenderedPage::find($html, STOCK_OLD_MARKUP),
        ];

        // The layout's own markup has neither; the pages of this group must not add them.
        if (preg_match('/\sstyle\s*=/i', $html) === 1) {
            $found[] = 'style attribute';
        }

        if (preg_match('/<[^>]+\son[a-z]+\s*=/i', $html) === 1) {
            $found[] = 'inline event handler';
        }

        foreach (RenderedPage::inlineScripts($html) as $script) {
            array_push($found, ...RenderedPage::find($script['code'], STOCK_SCRIPT_LEFTOVERS));
        }

        if (substr_count($html, '<title>') !== 1) {
            $found[] = substr_count($html, '<title>').' <title> elements';
        }

        foreach ($found as $pattern) {
            $problems[] = "{$name}: {$pattern}";
        }

        $rendered[$name] = $html;
    }

    expect($problems)->toBe([]);

    // The page scripts build their rows from <template>s whose field names the requests validate.
    expect($rendered['main.warehouse.receipts.goods.index'])
        ->toContain('<template id="receipt-medications-line">', 'name="medications[__LINE__][expiration_date]"', 'name="supplies[__LINE__][quantity]"', 'name="household[__LINE__][quantity]"')
        ->and($rendered['warehouse.order'])->toContain('name="warehouse_id"', 'name="items[__INDEX__][id]"', 'name="items[__INDEX__][quantity]"')
        ->and($rendered['transfers.create'])->toContain('<template id="medication-line-template" data-row-list-template>', 'data-row-list-min="1"', 'name="medications[__INDEX__][medication_id]"', 'name="medications[0][quantity]"')
        ->and($rendered['medications.create'])->toContain('<template id="additional-substance-template" data-row-list-template>', 'name="additional_substance[__INDEX__][substance_name]"', 'name="additional_substance[0][substance_dosage]"');

    if (! RenderedPage::nodeAvailable()) {
        $this->markTestIncomplete('Node is not installed: the inline scripts were not parsed.');
    }

    expect(RenderedPage::unparsableScripts($rendered))->toBe([]);
});

dataset('stock lists', [
    'devices' => ['devices', 'devices', fn (): Model => MedicalDevice::query()->create(['name' => 'Тонометр'])],
    'medication groups' => ['medication.groups', 'medication_groups', fn (): Model => MedicationGroup::factory()->create()],
    'medications' => ['medications', 'medications', fn (): Model => Medication::factory()->create()],
    'pharmacy types' => ['pharmacy.types', 'pharmacy_types', fn (): Model => PharmacyType::factory()->create()],
    'pharmacies' => ['pharmacies', 'pharmacies', fn (): Model => Pharmacy::factory()->create()],
    'warehouse types' => ['warehouses.type', 'warehouse_types', fn (): Model => WarehouseType::factory()->create()],
    'warehouses' => ['warehouses', 'warehouses', fn (): Model => Warehouse::factory()->create()],
    'supplies' => ['supplies', 'supplies', fn (): Model => MedicalSupply::factory()->create()],
    'household materials' => ['household.materials', 'household_materials', fn (): Model => HouseholdMaterial::factory()->create()],
    'manufacturers' => ['manufacturers', 'manufacturers', fn (): Model => Manufacturer::factory()->create()],
    'suppliers' => ['suppliers', 'suppliers', fn (): Model => Supplier::factory()->create()],
]);

it('offers the create button and the row actions of a stock list only to accounts that may use them', function (string $route, string $permission, Model $record): void {
    actingAs(adminWithPermissions("list_{$permission}"), 'admin')
        ->get(route("{$route}.index"))
        ->assertOk()
        ->assertSee($record->getAttribute('name'))
        ->assertDontSee(route("{$route}.create"))
        ->assertDontSee(route("{$route}.edit", $record))
        ->assertDontSeeHtml('action="'.route("{$route}.destroy", $record).'"');

    actingAs(adminWithPermissions("list_{$permission}", "create_{$permission}", "edit_{$permission}", "delete_{$permission}"), 'admin')
        ->get(route("{$route}.index"))
        ->assertOk()
        ->assertSee(route("{$route}.create"))
        ->assertSeeHtml('href="'.route("{$route}.edit", $record).'"')
        ->assertSeeHtml('action="'.route("{$route}.destroy", $record).'"')
        ->assertSeeHtml('data-confirm=');
})->with('stock lists');

it('changes the status of a pending transfer through the API route, and only for accounts that may edit it', function (): void {
    $transfer = Transfer::factory()->create();

    actingAs(adminWithPermissions('list_transfers'), 'admin')
        ->get(route('transfers.index'))
        ->assertOk()
        ->assertSeeText($transfer->status->label())
        ->assertDontSeeHtml(route('api.transfers.update-status', $transfer));

    actingAs(adminWithPermissions('list_transfers', 'edit_transfers'), 'admin')
        ->get(route('transfers.index'))
        ->assertOk()
        ->assertSeeHtml('data-url="'.route('api.transfers.update-status', $transfer).'"')
        ->assertSeeHtml('data-question="Завершить перевод?')
        ->assertSeeHtml('data-accept="Завершить перевод"')
        // Cancelling is final too (the transfer can no longer be edited or deleted): it asks first as well.
        ->assertSeeHtml('data-question="Отменить перевод №'.$transfer->id.'?')
        ->assertSeeHtml('data-accept="Отменить перевод"')
        ->assertSeeHtml('href="'.route('transfers.edit', $transfer).'"');
});

it('asks before writing off a batch of the main warehouse, and shows the action only with edit_main_warehouse', function (): void {
    seed(WarehouseSeeder::class);
    $batch = StockFixtures::mainBatch(Medication::factory()->create(['name' => 'Гепарин']), 40, StockFixtures::monthsAhead(6));
    $discard = route('main.warehouse.receipts.goods.discard', ['type' => 'medications', 'id' => $batch->id]);

    actingAs(adminWithPermissions('main_warehouse_remain'), 'admin')
        ->get(route('main.warehouse.remains'))
        ->assertOk()
        ->assertSeeText('Гепарин')
        ->assertDontSeeHtml($discard)
        ->assertDontSee(route('main.warehouse.receipts.goods.index'));

    actingAs(adminWithPermissions('main_warehouse_remain', 'edit_main_warehouse'), 'admin')
        ->get(route('main.warehouse.remains'))
        ->assertOk()
        ->assertSeeHtml('action="'.$discard.'"')
        ->assertSeeHtml('data-confirm="Списать со склада «Гепарин» (40 шт.)?"')
        ->assertSee(route('main.warehouse.receipts.goods.index'));
});

it('opens the card of a warehouse and of a medication whose details were never filled in', function (): void {
    actingAs(adminWithPermissions('view_warehouses', 'view_medications'), 'admin');

    get(route('warehouses.show', Warehouse::factory()->create(['name' => 'Пустой склад'])))
        ->assertOk()
        ->assertSeeText('Пустой склад');

    get(route('medications.show', Medication::factory()->create(['name' => 'Без описания'])))
        ->assertOk()
        ->assertSeeText('Документы не загружены.');
});
