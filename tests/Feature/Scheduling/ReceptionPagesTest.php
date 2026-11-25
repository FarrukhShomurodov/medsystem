<?php

use App\Enums\ReceptionStatus;
use App\Models\ExaminationMap;
use App\Models\Reception;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffProfessionalData;
use Carbon\CarbonImmutable;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Frontend\RenderedPage;
use Tests\Feature\Scheduling\SchedulingFixtures;

use function Pest\Laravel\actingAs;

/*
| The reception list, the booking grid and the doctor's calendar.
*/

/**
 * What a page hands its script: the JSON of its <script type="application/json" id="..."> block.
 *
 * @return array<string, mixed>
 */
function pageScriptData(TestResponse $response, string $id): array
{
    $found = preg_match('#<script type="application/json" id="'.preg_quote($id, '#').'">(.*?)</script>#s', (string) $response->getContent(), $match);

    expect($found)->toBe(1, "The page has no #{$id} data for its script.");

    return json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
}

it('lists the appointments of the doctor chosen in the filter', function (): void {
    // Regression: the filter used $request->filled('staff_id'), a boolean, as the staff id.
    $first = SchedulingFixtures::doctor();
    $chosen = SchedulingFixtures::doctor();
    $ofFirst = SchedulingFixtures::appointment($first, '09:00', '09:20', ['comment' => 'appointment-of-first']);
    $ofChosen = SchedulingFixtures::appointment($chosen, '09:00', '09:20', ['comment' => 'appointment-of-chosen']);

    actingAs(adminWithPermissions('list_receptions'), 'admin')
        ->get(route('receptions.index', ['staff_id' => $chosen->id]))
        ->assertOk()
        ->assertSee($ofChosen->comment)
        ->assertDontSee($ofFirst->comment)
        ->assertSeeHtml('name="staff_id"');
});

it('filters appointments by whether they belong to an examination map', function (): void {
    $map = ExaminationMap::factory()->create();
    Reception::factory()->create(['examination_map_id' => $map->id, 'comment' => 'inpatient-visit']);
    Reception::factory()->create(['comment' => 'one-off-visit']);
    $registrar = adminWithPermissions('list_receptions');

    actingAs($registrar, 'admin')->get(route('receptions.index', ['is_disposable' => 'no']))
        ->assertSee('inpatient-visit')->assertDontSee('one-off-visit');

    actingAs($registrar, 'admin')->get(route('receptions.index', ['is_disposable' => 'yes']))
        ->assertSee('one-off-visit')->assertDontSee('inpatient-visit');
});

it('shows a doctor only their own appointments, without the staff filter', function (Closure $doctorAccount): void {
    $doctorAdmin = $doctorAccount('list_receptions');
    $own = SchedulingFixtures::appointment($doctorAdmin->staff, '09:00', '09:20', ['comment' => 'own-visit']);
    $colleague = SchedulingFixtures::doctor();
    SchedulingFixtures::appointment($colleague, '09:00', '09:20', ['comment' => 'colleague-visit']);

    actingAs($doctorAdmin, 'admin')
        ->get(route('receptions.index', ['staff_id' => $colleague->id]))
        ->assertOk()
        ->assertSee($own->comment)
        ->assertDontSee('colleague-visit')
        ->assertDontSeeHtml('name="staff_id"');
})->with(SchedulingFixtures::doctorAccounts());

it('offers a doctor only their own appointment services on the booking page', function (Closure $doctorAccount): void {
    $doctorAdmin = $doctorAccount('view_appointment_receptions');
    $own = $doctorAdmin->staff->services()->first();
    $other = Service::factory()->appointment()->create(['name' => 'Чужая консультация']);

    actingAs($doctorAdmin, 'admin')
        ->get(route('receptions.appointment'))
        ->assertOk()
        ->assertSee($own->name)
        ->assertDontSee($other->name);

    $page = actingAs(adminWithPermissions('view_appointment_receptions'), 'admin')
        ->get(route('receptions.appointment'))
        ->assertOk()
        ->assertSee([$own->name, $other->name]);

    // The grid shows today's slots of the clinic, so the booking date is the server's today.
    expect(pageScriptData($page, 'appointment-data')['today'])->toBe(today()->toDateString());
})->with(SchedulingFixtures::doctorAccounts());

it('offers no booking for a service taken off the price list', function (): void {
    $doctorAdmin = SchedulingFixtures::doctorAccount('view_appointment_receptions', 'view_reception_doctor_calendar');
    $withdrawn = $doctorAdmin->staff->services()->first();
    $withdrawn->update(['name' => 'Снятая консультация', 'is_active' => false]);
    $admin = adminWithPermissions('view_appointment_receptions', 'view_reception_doctor_calendar');

    actingAs($doctorAdmin, 'admin')->get(route('receptions.appointment'))->assertOk()->assertDontSee('Снятая консультация');
    actingAs($admin, 'admin')->get(route('receptions.appointment'))->assertOk()->assertDontSee('Снятая консультация');

    actingAs($admin, 'admin')->getJson("/api/staff-by-service/{$withdrawn->id}")->assertOk()->assertExactJson([]);

    actingAs($admin, 'admin')
        ->get(route('receptions.doctor.calendar', [$doctorAdmin->staff, $withdrawn->id]))
        ->assertRedirect(route('receptions.appointment'))
        ->assertSessionHas('error', 'Услуга снята с прайс-листа: на неё не записывают.');
});

it('opens the doctor calendar for the service and refuses a doctor without an appointment length', function (): void {
    // A Sunday night: the browser's clock used to pick the week, the server's week starts on the 16th.
    $this->travelTo(CarbonImmutable::parse('2026-11-22 23:30'));
    $doctor = SchedulingFixtures::doctor(30);
    $service = $doctor->services()->first();
    $admin = adminWithPermissions('view_reception_doctor_calendar', 'view_appointment_receptions');

    $calendar = pageScriptData(
        actingAs($admin, 'admin')
            ->get(route('receptions.doctor.calendar', [$doctor, $service->id]))
            ->assertOk()
            ->assertDontSeeHtml('<option value="paid">'),
        'calendar-data',
    );

    expect($calendar['admissionPeriod'])->toBe(30)
        ->and($calendar['serviceId'])->toBe($service->id)
        ->and($calendar['weekStart'])->toBe('2026-11-16');

    actingAs(adminWithPermissions('view_reception_doctor_calendar', 'accept_payments'), 'admin')
        ->get(route('receptions.doctor.calendar', [$doctor, $service->id]))
        ->assertSeeHtml('<option value="paid">');

    actingAs($admin, 'admin')
        ->get(route('receptions.doctor.calendar', [$doctor, 0]))
        ->assertNotFound();

    StaffProfessionalData::query()->whereBelongsTo($doctor)->update(['admission_period' => null]);

    actingAs($admin, 'admin')
        ->get(route('receptions.doctor.calendar', [$doctor, $service->id]))
        ->assertRedirect(route('receptions.appointment'))
        ->assertSessionHas('error');
});

it('opens the doctor calendar only for an appointment service the employee offers', function (Closure $service): void {
    // Opened by URL, the calendar drew free slots for any service, and every booking made there was refused.
    $doctor = SchedulingFixtures::doctor();

    actingAs(adminWithPermissions('view_reception_doctor_calendar'), 'admin')
        ->get(route('receptions.doctor.calendar', [$doctor, $service($doctor)]))
        ->assertRedirect(route('receptions.appointment'))
        ->assertSessionHas('error', 'Сотрудник не ведёт приём по этой услуге: календарь недоступен.');
})->with([
    'an appointment service the employee does not offer' => fn (): Service => Service::factory()->appointment()->create(),
    'a service of the employee that is not booked by time' => fn (Staff $doctor): Service => tap(
        Service::factory()->create(),
        fn (Service $service) => $doctor->services()->attach($service),
    ),
]);

it('opens a doctor\'s account only the doctor\'s own calendar', function (Closure $doctorAccount): void {
    // The calendar of any employee opened by URL, though the booking page offers a doctor only their own.
    $doctorAdmin = $doctorAccount('view_reception_doctor_calendar');
    $service = $doctorAdmin->staff->services()->value('services.id');
    $colleague = SchedulingFixtures::doctor();
    $colleague->services()->attach($service);

    actingAs($doctorAdmin, 'admin')
        ->get(route('receptions.doctor.calendar', [$colleague, $service]))
        ->assertForbidden()
        ->assertSee('Врач работает только со своим календарём приёмов.');

    actingAs($doctorAdmin, 'admin')
        ->get(route('receptions.doctor.calendar', [$doctorAdmin->staff, $service]))
        ->assertOk();
})->with(SchedulingFixtures::doctorAccounts());

it('opens any employee\'s calendar for an account without a doctor profile', function (?int $position): void {
    $doctor = SchedulingFixtures::doctor();

    actingAs(SchedulingFixtures::account($position, 'view_reception_doctor_calendar'), 'admin')
        ->get(route('receptions.doctor.calendar', [$doctor, $doctor->services()->value('services.id')]))
        ->assertOk();
})->with(SchedulingFixtures::WITHOUT_DOCTOR_PROFILE);

it('shows the status of every appointment and narrows the list to a status or a day', function (): void {
    // The list had no status: the dashboard's "Отмена 1" led to a list where the cancelled
    // appointment looked like any other, and the day's cards led to the appointments of every day.
    $doctor = SchedulingFixtures::doctor();
    SchedulingFixtures::appointment($doctor, '09:00', '09:20', ['status' => ReceptionStatus::Cancelled, 'comment' => 'cancelled-today']);
    SchedulingFixtures::appointment($doctor, '09:20', '09:40', ['comment' => 'booked-today']);
    SchedulingFixtures::appointment($doctor, '09:00', '09:20', ['date' => today()->subDay()->toDateString(), 'comment' => 'booked-yesterday']);
    actingAs(adminWithPermissions('list_receptions'), 'admin');

    $rows = collect(RenderedPage::tableRows((string) $this->get(route('receptions.index'))->assertOk()->getContent(), '//main//table'))
        ->keyBy('Комментарий');

    expect($rows['cancelled-today']['Статус'])->toBe('Отменён')
        ->and($rows['booked-today']['Статус'])->toBe('Новый')
        ->and($rows['booked-today']['Дата'])->toBe(today()->format('d.m.Y'));

    $this->get(route('receptions.index', ['status' => ReceptionStatus::Cancelled->value]))
        ->assertOk()
        ->assertSee('cancelled-today')
        ->assertDontSee('booked-today')
        ->assertDontSee('booked-yesterday');

    $this->get(route('receptions.index', ['date' => today()->toDateString()]))
        ->assertOk()
        ->assertSee(['cancelled-today', 'booked-today'])
        ->assertDontSee('booked-yesterday');
});

it('sends an unknown status or a malformed day back to the unfiltered list', function (array $filter): void {
    actingAs(adminWithPermissions('list_receptions'), 'admin')
        ->get(route('receptions.index', $filter))
        ->assertRedirect(route('receptions.index'))
        ->assertSessionHasErrors(array_keys($filter));
})->with([
    'status' => [['status' => 'lost']],
    'day' => [['date' => '18.11.2026']],
]);
