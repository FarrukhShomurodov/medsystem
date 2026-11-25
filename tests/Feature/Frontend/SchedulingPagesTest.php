<?php

use App\Models\Admin;
use App\Models\Reception;
use App\Models\Specialty;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\StaffSchedule;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Database\Eloquent\Builder;
use Tests\Feature\Frontend\RenderedPage;
use Tests\Feature\Scheduling\SchedulingFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

/*
| The staff and reception screens (admins, staffs, staff_positions, specialties, receptions) after
| the move to Tabler: every page opens over the demo hospital without the old template, its inline
| scripts parse, and the controls follow the permissions and AdminPolicy.
*/

/** Class names and markup of the removed template that these pages must not use any more. */
const SCHEDULING_OLD_MARKUP = [
    'input-group-merge', 'icon-border', 'breadcrumb-wrapper', 'card-datatable', 'datatables-users',
    'btn-label-', 'bg-label-', 'errors_alert', 'basic-icon-default', 'class="fas ', 'class="bi ', 'onclick=', 'onchange=',
    'style="', '<style', 'moment(',
];

/**
 * The HTML inside <main> (the page's own markup, without the layout).
 */
function schedulingMain(string $html): string
{
    return preg_match('#<main\b.*?</main>#s', $html, $match) === 1 ? $match[0] : '';
}

it('renders every staff and reception page over the demo data without the old template', function (): void {
    seed(DatabaseSeeder::class);
    seed(DemoSeeder::class);
    actingAs(Admin::query()->where('email', 'admin@medsystem.test')->firstOrFail(), 'admin');

    $doctor = Staff::query()
        ->whereRelation('professionalData', 'admission_period', '>', 0)
        ->whereHas('services', fn (Builder $service) => $service->where('is_appointment', true))
        ->firstOrFail();
    $staff = Staff::query()->orderBy('id')->firstOrFail();

    $pages = [
        'admins.index' => route('admins.index'),
        'admins.create' => route('admins.create'),
        'admins.edit' => route('admins.edit', Admin::query()->orderBy('id')->firstOrFail()),
        'specialties.index' => route('specialties.index'),
        'specialties.create' => route('specialties.create'),
        'specialties.edit' => route('specialties.edit', Specialty::query()->orderBy('id')->firstOrFail()),
        'staffs.position.index' => route('staffs.position.index'),
        'staffs.position.create' => route('staffs.position.create'),
        'staffs.position.edit' => route('staffs.position.edit', StaffPosition::query()->orderBy('id')->firstOrFail()),
        'staffs.index' => route('staffs.index'),
        'staffs.create' => route('staffs.create'),
        'staffs.show' => route('staffs.show', $staff),
        'staffs.edit' => route('staffs.edit', $staff),
        'receptions.index' => route('receptions.index'),
        'receptions.appointment' => route('receptions.appointment'),
        'receptions.doctor.calendar' => route('receptions.doctor.calendar', [$doctor, $doctor->services()->where('is_appointment', true)->value('services.id')]),
    ];

    $problems = [];
    $rendered = [];

    foreach ($pages as $name => $url) {
        $response = get($url);
        $html = (string) $response->getContent();

        if ($response->getStatusCode() !== 200) {
            $problems[] = "{$name}: status {$response->getStatusCode()}";

            continue;
        }

        $main = schedulingMain($html);
        $found = [
            ...RenderedPage::find($html, RenderedPage::TEMPLATE_PATTERNS),
            ...RenderedPage::find($html, RenderedPage::CDN_PATTERNS),
            ...RenderedPage::find($main, SCHEDULING_OLD_MARKUP),
        ];

        foreach ($found as $pattern) {
            $problems[] = "{$name}: contains {$pattern}";
        }

        preg_match_all('/\sid="([^"]+)"/', $main, $ids);

        foreach (array_unique(array_diff_assoc($ids[1], array_unique($ids[1]))) as $id) {
            $problems[] = "{$name}: the id \"{$id}\" is used twice";
        }

        if (! str_contains($html, '<h1 class="page-title">')) {
            $problems[] = "{$name}: no page header";
        }

        $rendered[$name] = $html;
    }

    expect($problems)->toBe([]);

    if (! RenderedPage::nodeAvailable()) {
        $this->markTestIncomplete('Node is not installed: the inline scripts were not parsed.');
    }

    expect(RenderedPage::unparsableScripts($rendered))->toBe([]);
});

it('offers the edit and delete actions of a main administrator\'s account only to a main administrator', function (): void {
    // AdminPolicy: edit_admins / delete_admins alone used to show buttons that led to a 403.
    $manager = adminWithPermissions('list_admins', 'edit_admins', 'delete_admins');
    $chief = Admin::factory()->mainAdmin()->create();
    $nurse = Admin::factory()->create();

    actingAs($manager, 'admin')->get(route('admins.index'))
        ->assertOk()
        ->assertSeeHtml('href="'.route('admins.edit', $nurse).'"')
        ->assertSeeHtml('action="'.route('admins.destroy', $nurse).'"')
        ->assertDontSeeHtml('href="'.route('admins.edit', $chief).'"')
        ->assertDontSeeHtml('action="'.route('admins.destroy', $chief).'"')
        // Nobody deletes the account they are signed in with.
        ->assertDontSeeHtml('action="'.route('admins.destroy', $manager).'"')
        ->assertDontSeeHtml('href="'.route('admins.create').'"');

    $manager->assignRole(Admin::MAIN_ADMIN_ROLE);

    actingAs($manager->fresh(), 'admin')->get(route('admins.index'))
        ->assertSeeHtml('href="'.route('admins.edit', $chief).'"')
        ->assertSeeHtml('action="'.route('admins.destroy', $chief).'"');
});

it('offers the actions on an employee whose login is a main administrator only to a main administrator', function (): void {
    SchedulingFixtures::seedWeekdays();
    $manager = adminWithPermissions('list_staffs', 'edit_staffs', 'delete_staffs');
    $chief = Staff::factory()->nurse()->create();
    Admin::factory()->mainAdmin()->forStaff($chief)->create();
    $nurse = Staff::factory()->nurse()->create();

    actingAs($manager, 'admin')->get(route('staffs.index'))
        ->assertOk()
        ->assertSeeHtml('href="'.route('staffs.edit', $nurse).'"')
        ->assertSeeHtml('action="'.route('staffs.destroy', $nurse).'"')
        ->assertDontSeeHtml('href="'.route('staffs.edit', $chief).'"')
        ->assertDontSeeHtml('action="'.route('staffs.destroy', $chief).'"')
        ->assertSeeHtml('href="'.route('staffs.show', $chief).'"');

    $manager->assignRole(Admin::MAIN_ADMIN_ROLE);

    actingAs($manager->fresh(), 'admin')->get(route('staffs.index'))
        ->assertSeeHtml('href="'.route('staffs.edit', $chief).'"')
        ->assertSeeHtml('action="'.route('staffs.destroy', $chief).'"');
});

it('keeps the fields of the employee form and sends no hours of a day off', function (): void {
    SchedulingFixtures::seedWeekdays();
    $manager = adminWithPermissions('create_staffs', 'edit_staffs');

    $create = (string) actingAs($manager, 'admin')->get(route('staffs.create'))->assertOk()->getContent();

    foreach (['name', 'last_name', 'middle_name', 'date_birthday', 'gender', 'citizenship', 'login_email', 'password', 'avatar_url', 'sign_url',
        'phone', 'address', 'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation', 'position_id', 'addition_position_id',
        'speciality_id', 'category', 'department_id', 'services[]', 'education_name', 'education_graduation_year', 'certificate_name',
        'certificate_number', 'certificate_issue_date', 'certificate_validity_period', 'employment_contract_number',
        'employment_contract_date_of_signing', 'employment_contract_validity_period', 'passport_series', 'INN', 'SNILS', 'surveys[]',
        'admission_period', 'days[0][id]', 'days[0][work][0][start_time]', 'days[0][work][0][end_time]',
        'days[0][reception][0][start_time]', 'days[0][reception][0][end_time]'] as $field) {
        expect($create)->toContain('name="'.$field.'"');
    }

    expect($create)->toContain('action="'.route('staffs.store').'"')
        ->toContain('enctype="multipart/form-data"')
        ->not->toContain('name="absence[')
        ->not->toContain('name="dismissal_orders_number"');

    // An employee working on Monday only, without reception hours.
    $staff = Staff::factory()->nurse()->create();
    StaffSchedule::factory()->for($staff)->create(['day_of_week_id' => 1, 'start_time' => '09:00', 'end_time' => '15:00']);

    $edit = (string) actingAs($manager, 'admin')->get(route('staffs.edit', $staff))->assertOk()->getContent();

    expect($edit)
        ->toMatch('/name="days\[0\]\[id\]" value="1" data-day-id\s*>/')
        ->toMatch('/name="days\[1\]\[id\]" value="2" data-day-id\s+disabled>/')
        ->toMatch('/name="days\[1\]\[work\]\[0\]\[start_time\]" value="08:30"\s+disabled>/')
        ->toMatch('/id="disable-reception" data-reception-off checked>/')
        ->toContain('name="dismissal_orders_number"')
        ->toContain('id="absence-template"');
});

it('hands the booking grids their data and offers only the actions the account may take', function (): void {
    $doctor = SchedulingFixtures::doctor(30);
    $service = $doctor->services()->value('services.id');
    Reception::factory()->create(['staff_id' => $doctor->id, 'service_id' => $service, 'start_time' => '09:00', 'end_time' => '09:30']);

    $viewer = adminWithPermissions('view_appointment_receptions', 'view_reception_doctor_calendar');
    $calendar = (string) actingAs($viewer, 'admin')->get(route('receptions.doctor.calendar', [$doctor, $service]))->assertOk()->getContent();

    preg_match('#<script type="application/json" id="calendar-data">(.*?)</script>#s', $calendar, $block);
    $data = json_decode($block[1], true, flags: JSON_THROW_ON_ERROR);

    expect($data)->toMatchArray([
        'serviceId' => $service,
        'admissionPeriod' => 30,
        'weekStart' => today()->startOfWeek()->toDateString(),
        'today' => today()->toDateString(),
        'canBook' => false,
        'canCancel' => false,
        'cancelled' => 'cancelled',
    ])
        ->and($data['doctor']['id'])->toBe($doctor->id)
        ->and($data['urls']['week'])->toBe(route('api.staff.availability-week', ['staff' => $doctor->id], false))
        ->and($data['urls']['show'])->toContain('__RECEPTION__')
        ->and($calendar)->not->toContain('data-cancel-reception');

    $clerk = adminWithPermissions('view_appointment_receptions', 'create_receptions', 'change_receptions_status');
    $appointment = (string) actingAs($clerk, 'admin')->get(route('receptions.appointment'))->assertOk()->getContent();

    preg_match('#<script type="application/json" id="appointment-data">(.*?)</script>#s', $appointment, $block);
    $data = json_decode($block[1], true, flags: JSON_THROW_ON_ERROR);

    expect($data)->toMatchArray(['today' => today()->toDateString(), 'canBook' => true, 'canCancel' => true, 'canOpenCalendar' => false])
        ->and($data['urls']['doctors'])->toBe(route('api.staff.by-service', ['service' => '__SERVICE__'], false))
        ->and($appointment)->toContain('data-cancel-reception')
        ->toContain('<option value="'.$service.'">');
});
