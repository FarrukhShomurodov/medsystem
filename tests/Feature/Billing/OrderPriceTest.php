<?php

use App\Models\Patient;
use App\Models\PatientProcedure;
use App\Models\PatientService;
use App\Models\Procedure;
use App\Models\Reception;
use App\Models\Service;
use App\Queries\Billing\BillableItem;
use App\Queries\Billing\RegistrarBasketQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Scheduling\SchedulingFixtures;

use function Pest\Laravel\actingAs;

/*
| An order keeps the price it was placed at: the registrar and the cash desk charge that one,
| whatever the price list says by the time the patient pays.
*/

beforeEach(function (): void {
    $this->patient = Patient::factory()->create();

    actingAs(adminWithPermissions(
        'create_patient_services',
        'edit_patient_services',
        'create_patient_procedure',
        'create_receptions',
        'confirm_patient_registration',
        'view_cashier',
    ), 'admin');
});

/**
 * What the cash desk charges for the orders waiting for payment, by order.
 *
 * @return array<string, string>
 */
function chargedAtCashDesk(): array
{
    return test()->get(route('cashier.index'))->assertOk()->viewData('records')->getCollection()
        ->mapWithKeys(fn (BillableItem $item): array => [$item->key() => $item->price->toDecimal()])
        ->all();
}

it('charges services, procedures and appointments at the price they were ordered at', function (): void {
    // The cash desk read the current price of the price list: raising a price after ordering
    // raised what the patient paid for an order placed earlier.
    $service = Service::factory()->create(['price' => '150000.00']);
    $procedure = Procedure::factory()->create(['price' => '40000.00']);
    $doctor = SchedulingFixtures::doctor();
    $consultation = $doctor->services()->sole();
    $consultation->update(['price' => '90000.00']);

    $this->post(route('patient.services.store'), [
        'service_id' => $service->id,
        'patient_id' => $this->patient->id,
        'service_date' => today()->toDateString(),
    ])->assertSessionHasNoErrors();
    $this->post(route('patient.procedures.store'), [
        'patient_id' => $this->patient->id,
        'procedures' => [$procedure->id],
        'procedure_dates' => [today()->toDateString()],
    ])->assertSessionHasNoErrors();
    $this->postJson('/api/receptions', SchedulingFixtures::booking($doctor, '10:00', '10:20', ['patient_id' => $this->patient->id]))
        ->assertCreated();

    foreach ([$service, $procedure, $consultation] as $entry) {
        $entry->update(['price' => '999999.00']);
    }

    $ordered = [
        'service:'.PatientService::query()->sole()->id => '150000.00',
        'procedure:'.PatientProcedure::query()->sole()->id => '40000.00',
        'reception:'.Reception::query()->sole()->id => '90000.00',
    ];

    expect(app(RegistrarBasketQuery::class)->items($this->patient)->mapWithKeys(fn (BillableItem $item): array => [$item->key() => $item->price->toDecimal()])->all())
        ->toEqualCanonicalizing($ordered);

    $this->post(route('registrar.confirm', $this->patient))->assertSessionHas('success', 'Подтверждено записей: 3.');

    expect(chargedAtCashDesk())->toEqualCanonicalizing($ordered);
});

it('charges a service changed before payment at the new service\'s price', function (): void {
    $order = PatientService::factory()->confirmed()->for($this->patient)->create();
    $ultrasound = Service::factory()->create(['price' => '300000.00']);

    $this->put(route('patient.services.update', $order), [
        'service_id' => $ultrasound->id,
        'patient_id' => $this->patient->id,
        'service_date' => $order->service_date,
    ])->assertSessionHasNoErrors();

    $ultrasound->update(['price' => '500000.00']);

    expect(chargedAtCashDesk())->toBe(["service:{$order->id}" => '300000.00']);
});

it('charges orders without a kept price at the current price of the price list', function (): void {
    // Orders written by code older than the price column.
    $service = PatientService::factory()->confirmed()->create(['price' => null]);
    $service->service->update(['price' => '70000.00']);

    expect(chargedAtCashDesk())->toBe(["service:{$service->id}" => '70000.00']);
});

it('gives the existing orders the current price of the price list, and drops the column on rollback', function (): void {
    $service = PatientService::factory()->create();
    $procedure = PatientProcedure::factory()->create();
    $reception = Reception::factory()->create();
    $service->service->update(['price' => '11000.00']);
    $procedure->procedure->update(['price' => '22000.00']);
    $reception->service->update(['price' => '33000.00']);
    $migration = require database_path('migrations/2025_11_20_120000_add_price_to_patient_orders.php');

    $migration->down();

    expect(Schema::hasColumn('patient_services', 'price'))->toBeFalse()
        ->and(Schema::hasColumn('patient_procedures', 'price'))->toBeFalse()
        ->and(Schema::hasColumn('receptions', 'price'))->toBeFalse();

    $migration->up();

    expect(DB::table('patient_services')->where('id', $service->id)->value('price'))->toBe('11000.00')
        ->and(DB::table('patient_procedures')->where('id', $procedure->id)->value('price'))->toBe('22000.00')
        ->and(DB::table('receptions')->where('id', $reception->id)->value('price'))->toBe('33000.00');
});

it('bills services and appointments in the currency their price was ordered in', function (): void {
    // The kept price was read in the price list's current currency: moving a service from sums to
    // dollars billed an earlier order of 150 000 sums as 150 000 dollars.
    $service = Service::factory()->create(['price' => '150000.00', 'currency' => ' uzs ']);
    $doctor = SchedulingFixtures::doctor();
    $consultation = $doctor->services()->sole();
    $consultation->update(['price' => '90000.00', 'currency' => null]);

    $this->post(route('patient.services.store'), [
        'service_id' => $service->id,
        'patient_id' => $this->patient->id,
        'service_date' => today()->toDateString(),
    ])->assertSessionHasNoErrors();
    $this->postJson('/api/receptions', SchedulingFixtures::booking($doctor, '10:00', '10:20', ['patient_id' => $this->patient->id]))
        ->assertCreated();

    foreach ([$service, $consultation] as $entry) {
        $entry->update(['price' => '15.00', 'currency' => 'USD']);
    }

    $this->post(route('registrar.confirm', $this->patient))->assertSessionHas('success', 'Подтверждено записей: 2.');

    expect(test()->get(route('cashier.index'))->assertOk()->viewData('records')->getCollection()
        ->mapWithKeys(fn (BillableItem $item): array => [$item->key() => "{$item->price->toDecimal()} {$item->currency}"])->all())
        ->toEqualCanonicalizing([
            'service:'.PatientService::query()->sole()->id => '150000.00 UZS',
            'reception:'.Reception::query()->sole()->id => '90000.00 UZS',
        ]);
});

it('gives the existing orders the currency of the price list as billing reads it, and drops the column on rollback', function (): void {
    $service = PatientService::factory()->create();
    $reception = Reception::factory()->create();
    $service->service->update(['currency' => ' usd ']);
    $reception->service->update(['currency' => null]);
    $migration = require database_path('migrations/2025_11_20_130000_add_currency_to_patient_orders.php');

    $migration->down();

    expect(Schema::hasColumn('patient_services', 'currency'))->toBeFalse()
        ->and(Schema::hasColumn('receptions', 'currency'))->toBeFalse();

    $migration->up();

    expect(DB::table('patient_services')->where('id', $service->id)->value('currency'))->toBe('USD')
        ->and(DB::table('receptions')->where('id', $reception->id)->value('currency'))->toBe('UZS');
});
