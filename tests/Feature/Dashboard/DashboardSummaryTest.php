<?php

use App\Enums\PatientMedicationStatus;
use App\Enums\ReceptionStatus;
use App\Enums\ServiceStatus;
use App\Enums\SupplyRequestStatus;
use App\Models\Admin;
use App\Models\Department;
use App\Models\ExaminationMap;
use App\Models\HouseholdMaterial;
use App\Models\MainWarehouse;
use App\Models\MainWarehouseHouseholdMaterial;
use App\Models\MainWarehouseMedicalSupply;
use App\Models\MedicalSupply;
use App\Models\Medication;
use App\Models\PatientMedication;
use App\Models\PatientProcedure;
use App\Models\PatientService;
use App\Models\Reception;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\SupplyRequest;
use App\Queries\Dashboard\Dashboard;
use App\Queries\Dashboard\DashboardSummary;
use App\Services\Billing\Amount;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Stock\StockFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

/*
| The figures of the dashboard home (App\Queries\Dashboard\DashboardSummary) over a day of a small
| hospital made with the factories: each figure counts what its page lists, and the page costs the
| same number of queries however busy the day is.
*/

/**
 * A day of the hospital: stays, appointments, orders at the registrar and the cashier, stock.
 *
 * @return array<string, mixed> the records the tests look at
 */
function hospitalDay(): array
{
    $today = today();
    $yesterday = today()->subDay()->toDateString();
    $tomorrow = today()->addDay()->toDateString();

    // Stays: three open, one closed.
    $maps = collect([3, 1, 2])->map(fn (int $daysAgo) => ExaminationMap::factory()->create(['admission_date' => $today->copy()->subDays($daysAgo)->toDateString()]));
    $closed = ExaminationMap::factory()->discharged()->create();

    // Appointments of the day in every status, and one of yesterday.
    $appointments = collect([
        ['10:00', ReceptionStatus::Done],
        ['09:00', ReceptionStatus::New],
        ['11:00', ReceptionStatus::New],
        ['09:30', ReceptionStatus::Confirmed],
        ['12:00', ReceptionStatus::Paid],
        ['08:00', ReceptionStatus::Cancelled],
    ])->map(fn (array $slot) => Reception::factory()->create([
        'start_time' => $slot[0],
        'end_time' => Carbon::parse($slot[0])->addMinutes(20)->format('H:i'),
        'status' => $slot[1],
        'price' => '150000.00',
        'currency' => 'UZS',
    ]));
    Reception::factory()->create(['date' => $yesterday]);

    // The registrar's queue: the two new appointments above, two more orders of the first one's
    // patient (one on their open stay), and orders that do not count.
    $first = $appointments[1]->patient;
    $firstStay = ExaminationMap::factory()->for($first)->create(['admission_date' => $today->toDateString()]);
    PatientService::factory()->for($first)->create(['examination_map_id' => $firstStay->id]);
    PatientMedication::factory()->for($first)->create();
    PatientService::factory()->for($first)->create(['service_date' => $tomorrow]);
    PatientService::factory()->for($closed->patient)->create(['examination_map_id' => $closed->id]);
    PatientProcedure::factory()->create(['examination_map_id' => $firstStay->id]);

    // The cashier: four orders of the day to pay (with the confirmed appointment), four paid
    // (with the paid appointment and the one done after its payment), in two currencies; others
    // of other days.
    PatientService::factory()->confirmed()->create(['price' => '100000.50', 'currency' => 'UZS']);
    PatientProcedure::factory()->confirmed()->create(['price' => '20000.00']);
    PatientMedication::factory()->sentToFloor()->create(['price' => '7500.00']);
    PatientService::factory()->confirmed()->create(['service_date' => $tomorrow]);
    PatientService::factory()->create(['status' => ServiceStatus::Paid, 'price' => '20.00', 'currency' => 'USD']);
    PatientMedication::factory()->create(['status' => PatientMedicationStatus::ReceivedAtStation, 'price' => '5000.00']);
    PatientService::factory()->create(['status' => ServiceStatus::Paid, 'service_date' => $yesterday]);

    // Stock: two supply requests to decide, batches around their warning date, items run out.
    SupplyRequest::factory()->count(2)->create();
    SupplyRequest::factory()->create(['status' => SupplyRequestStatus::Approved]);

    $month = Medication::factory()->create(['notify_before_days' => 30]);
    $twoMonths = Medication::factory()->create(['notify_before_days' => 60]);
    $inDays = fn (int $days): string => $today->copy()->addDays($days)->toDateString();

    StockFixtures::mainBatch($month, 10, $inDays(10));        // due
    StockFixtures::mainBatch($month, 10, $inDays(30));        // due: the last day of the warning
    StockFixtures::mainBatch($month, 10, $inDays(40));        // not yet
    StockFixtures::mainBatch($twoMonths, 10, $inDays(40));    // due: this drug warns two months ahead
    StockFixtures::mainBatch($month, 5, $inDays(-2));         // expired, waiting to be written off
    StockFixtures::mainBatch($month, 0, $inDays(-5));         // used up: nothing to warn about

    $ward = StockFixtures::departmentWarehouse();
    StockFixtures::departmentBatch($ward, $month, 10, $inDays(5));
    StockFixtures::departmentBatch($ward, $month, 10, $inDays(90));

    $usedUp = Medication::factory()->create();
    StockFixtures::mainBatch($usedUp, 0, $inDays(100));
    StockFixtures::mainBatch($usedUp, 0, $inDays(200));

    foreach ([0, 5] as $quantity) {
        MainWarehouseMedicalSupply::query()->create(['main_warehouse_id' => MainWarehouse::MAIN, 'supply_id' => MedicalSupply::factory()->create()->id, 'quantity' => $quantity]);
    }

    MainWarehouseHouseholdMaterial::query()->create(['main_warehouse_id' => MainWarehouse::MAIN, 'household_material_id' => HouseholdMaterial::factory()->create()->id, 'quantity' => 0]);

    return ['maps' => $maps, 'appointments' => $appointments, 'first' => $first, 'second' => $appointments[2]->patient, 'firstStay' => $firstStay];
}

function dashboardOf(Admin $viewer): Dashboard
{
    return app(DashboardSummary::class)->for($viewer, today()->toImmutable());
}

beforeEach(function (): void {
    // The roles and permissions, the staff positions and the main warehouse.
    seed(DatabaseSeeder::class);
    $this->admin = Admin::factory()->mainAdmin()->create();
});

it('counts the patients with an open stay and lists the latest admissions first', function (): void {
    $day = hospitalDay();
    $dashboard = dashboardOf($this->admin);

    // The three stays of the day's fixture and the first patient's own one.
    expect($dashboard->inpatients)->toBe(4)
        ->and($dashboard->inpatientsOfAllDepartments)->toBeTrue()
        ->and($dashboard->admissions?->pluck('id')->all())->toBe([
            $day['firstStay']->id, $day['maps'][1]->id, $day['maps'][2]->id, $day['maps'][0]->id,
        ]);
});

it('counts the patients of a nurse\'s own department only, as the nurse window lists them', function (): void {
    $ward = Department::factory()->create();
    ExaminationMap::factory()->count(2)->create(['department_id' => $ward->id]);
    ExaminationMap::factory()->create();
    ExaminationMap::factory()->discharged()->create(['department_id' => $ward->id]);

    $nurse = adminWithPermissions('view_dashboard', 'sister_window_show');
    $nurse->update(['staff_id' => Staff::factory()->nurse(['department_id' => $ward->id])->create()->id]);

    $dashboard = dashboardOf($nurse);

    expect($dashboard->inpatients)->toBe(2)
        ->and($dashboard->inpatientsOfAllDepartments)->toBeFalse();

    actingAs($nurse, 'admin')->get(route('dashboard'))->assertOk()->assertSee('в вашем отделении');
});

it('counts the day\'s appointments by status and lists the booked ones in the order of the day', function (): void {
    $day = hospitalDay();
    $receptions = dashboardOf($this->admin)->receptions;
    $appointments = dashboardOf($this->admin)->appointments;

    expect($receptions?->count(ReceptionStatus::New))->toBe(2)
        ->and($receptions?->count(ReceptionStatus::Cancelled))->toBe(1)
        ->and($receptions?->booked())->toBe(5)
        ->and($receptions?->statuses())->toBe(ReceptionStatus::cases())
        ->and($receptions?->ownOnly)->toBeFalse()
        ->and($appointments?->pluck('id')->all())->toBe([
            $day['appointments'][1]->id, $day['appointments'][3]->id, $day['appointments'][0]->id,
            $day['appointments'][2]->id, $day['appointments'][4]->id,
        ]);
});

it('shows a doctor their own appointments, as their reception list does', function (): void {
    $doctor = Staff::factory()->doctor()->create();
    $doctor->professionalData()->update(['position_id' => StaffPosition::DOCTOR]);
    $account = tap(adminWithPermissions('view_dashboard', 'list_receptions'))->update(['staff_id' => $doctor->id]);

    $own = Reception::factory()->for($doctor)->create(['start_time' => '09:00', 'end_time' => '09:20']);
    Reception::factory()->for($doctor)->create(['start_time' => '09:20', 'end_time' => '09:40', 'status' => ReceptionStatus::Cancelled]);
    Reception::factory()->create(['start_time' => '09:00', 'end_time' => '09:20']);

    $dashboard = dashboardOf($account);

    expect($dashboard->receptions?->ownOnly)->toBeTrue()
        ->and($dashboard->receptions?->booked())->toBe(1)
        ->and($dashboard->appointments?->modelKeys())->toBe([$own->id]);

    actingAs($account, 'admin')->get(route('dashboard'))->assertOk()
        ->assertSee('Мои приёмы сегодня')
        ->assertDontSee($doctor->full_name);
});

it('sums the day\'s orders at the cashier per currency, as the cashier list of the day holds them', function (): void {
    hospitalDay();
    $dashboard = dashboardOf($this->admin);

    $sums = fn (array $amounts): array => array_map(fn (Amount $amount): int => $amount->minorUnits, $amounts);

    // Confirmed appointment 150 000 + service 100 000,50 + procedure 20 000 + prescription 7 500.
    expect($dashboard->awaitingPayment?->orders)->toBe(4)
        ->and($sums($dashboard->awaitingPayment?->sums->all() ?? []))->toBe(['UZS' => 27_750_050])
        // Paid appointment 150 000, the appointment that took place after its payment 150 000,
        // prescription 5 000, and a service of 20 dollars.
        ->and($dashboard->paid?->orders)->toBe(4)
        ->and($sums($dashboard->paid?->sums->all() ?? []))->toBe(['UZS' => 30_500_000, 'USD' => 2_000]);

    $date = today()->toDateString();

    actingAs($this->admin, 'admin')->get(route('dashboard'))
        ->assertOk()
        ->assertSee("заказа на 277\u{00A0}500,50 UZS")
        ->assertSee("305\u{00A0}000,00 UZS")
        ->assertSee('20,00 USD')
        ->assertSeeHtml('href="'.e(route('cashier.index', ['service_date' => $date])).'"')
        ->assertSeeHtml('href="'.e(route('cashier.index', ['status' => 'paid', 'service_date' => $date])).'"');

    // The cashier list the tiles open holds those very orders.
    $listed = fn (array $filters): int => actingAs($this->admin, 'admin')->get(route('cashier.index', $filters))->viewData('records')->total();

    expect($listed(['service_date' => $date]))->toBe(4)
        ->and($listed(['status' => 'paid', 'service_date' => $date]))->toBe(4);
});

it('counts the day\'s orders waiting for the registrar, by patient, as their baskets hold them', function (): void {
    $day = hospitalDay();
    $queue = dashboardOf($this->admin)->registrarQueue;

    // The first patient: the new appointment, the service on their open stay, the prescription.
    // Not counted: tomorrow's service, the service of a closed stay, the procedure placed on
    // another patient's stay.
    expect($queue?->orders)->toBe(4)
        ->and($queue?->patients)->toBe(2)
        ->and(array_map(fn (array $entry): array => [$entry['patient']->id, $entry['orders']], $queue->first ?? []))->toBe([
            [$day['first']->id, 3],
            [$day['second']->id, 1],
        ]);

    actingAs($this->admin, 'admin')->get(route('dashboard'))
        ->assertOk()
        ->assertSeeHtml('href="'.route('patients.show', $day['first']).'#registration"');
});

it('counts the stock to act on: supply requests, batches near or past expiry, items run out', function (): void {
    hospitalDay();
    $dashboard = dashboardOf($this->admin);

    expect($dashboard->pendingSupplyRequests)->toBe(2)
        ->and($dashboard->mainWarehouseExpiry?->due)->toBe(4)
        ->and($dashboard->mainWarehouseExpiry?->expired)->toBe(1)
        ->and($dashboard->wardWarehouseExpiry?->due)->toBe(1)
        ->and($dashboard->wardWarehouseExpiry?->expired)->toBe(0)
        // The used-up medication, the supply and the household material at zero.
        ->and($dashboard->outOfStock)->toBe(3);
});

it('computes nothing for the cards an account does not see', function (): void {
    hospitalDay();
    $dashboard = dashboardOf(adminWithPermissions('view_dashboard', 'view_cashier'));

    expect($dashboard->paid)->not->toBeNull()
        ->and($dashboard->inpatients)->toBeNull()
        ->and($dashboard->receptions)->toBeNull()
        ->and($dashboard->appointments)->toBeNull()
        ->and($dashboard->admissions)->toBeNull()
        ->and($dashboard->registrarQueue)->toBeNull()
        ->and($dashboard->pendingSupplyRequests)->toBeNull()
        ->and($dashboard->mainWarehouseExpiry)->toBeNull()
        ->and($dashboard->outOfStock)->toBeNull();
});

it('shows empty states on a day without appointments, admissions or orders', function (): void {
    actingAs($this->admin, 'admin')->get(route('dashboard'))->assertOk()
        ->assertSee('На сегодня приёмов нет')
        ->assertSee('Сейчас в стационаре никого нет')
        ->assertSee('Всё подтверждено')
        ->assertSee('0,00 UZS');
});

it('loads the page with the same number of queries however busy the day is', function (): void {
    $count = 0;
    $counting = false;

    DB::listen(function (QueryExecuted $query) use (&$count, &$counting): void {
        $count += $counting ? 1 : 0;
    });

    $queries = function () use (&$count, &$counting): int {
        [$count, $counting] = [0, true];
        actingAs($this->admin, 'admin')->get(route('dashboard'))->assertOk();
        $counting = false;

        return $count;
    };

    hospitalDay();
    // The first request also loads the permissions into their cache.
    $queries();
    $quiet = $queries();

    hospitalDay();
    hospitalDay();

    // One or two queries a card (a list one more per eager-loaded relation), the account and its
    // permissions: a bound well above that, and below a query per record of a busy day.
    expect($queries())->toBe($quiet)
        ->and($quiet)->toBeLessThan(30);
});
