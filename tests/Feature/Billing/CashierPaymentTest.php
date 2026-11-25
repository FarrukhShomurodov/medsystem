<?php

use App\Enums\PatientMedicationStatus;
use App\Enums\ReceptionStatus;
use App\Enums\ServiceStatus;
use App\Models\Admin;
use App\Models\Patient;
use App\Models\PatientMedication;
use App\Models\PatientProcedure;
use App\Models\PatientService;
use App\Models\Procedure;
use App\Models\Reception;
use App\Queries\Billing\CashierFilter;
use App\Queries\Billing\CashierItemsQuery;
use App\Services\Billing\PaymentState;
use Carbon\CarbonInterface;
use Database\Seeders\PermissionSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

/*
| "Оплатить выбранные" on the cashier page: the ticked orders of one patient are paid, all or none.
*/

beforeEach(function (): void {
    $this->cashier = adminWithPermissions('view_cashier', 'accept_payments');
    $this->patient = Patient::factory()->create();

    $this->pay = fn (array $records, ?Admin $admin = null) => actingAs($admin ?? $this->cashier, 'admin')
        ->from(route('cashier.index'))
        ->put(route('cashier.updateAll'), ['records' => $records]);
});

it('pays a mixed basket: services, receptions and procedures become paid, prescriptions go to the nurse station', function (): void {
    $service = PatientService::factory()->confirmed()->for($this->patient)->create();
    $reception = Reception::factory()->confirmed()->for($this->patient)->create();
    $procedure = PatientProcedure::factory()->confirmed()->for($this->patient)->create();
    $medication = PatientMedication::factory()->sentToFloor()->for($this->patient)->create();

    ($this->pay)([
        "service:{$service->id}",
        "reception:{$reception->id}",
        "procedure:{$procedure->id}",
        "medication:{$medication->id}",
    ])
        ->assertRedirect(route('cashier.index'))
        ->assertSessionHas('success', 'Оплачено записей: 4.');

    expect($service->fresh()->status)->toBe(ServiceStatus::Paid)
        ->and($reception->fresh()->status)->toBe(ReceptionStatus::Paid)
        ->and($procedure->fresh()->status)->toBe(ServiceStatus::Paid)
        ->and($medication->fresh()->status)->toBe(PatientMedicationStatus::ReceivedAtStation);
});

it('pays the procedure ordered for the patient, not the price list entry with the same id', function (): void {
    $procedure = PatientProcedure::factory()->confirmed()->for($this->patient)->create();
    $priceListEntry = Procedure::query()->findOrFail($procedure->procedure_id);

    ($this->pay)(["procedure:{$procedure->id}"])->assertSessionHas('success');

    expect($procedure->fresh()->status)->toBe(ServiceStatus::Paid)
        ->and($priceListEntry->fresh()->only('name', 'price'))->toBe($priceListEntry->only('name', 'price'));
});

it('refuses a repeated payment request instead of applying it twice', function (): void {
    $service = PatientService::factory()->confirmed()->for($this->patient)->create();
    $medication = PatientMedication::factory()->sentToFloor()->for($this->patient)->create();
    $records = ["service:{$service->id}", "medication:{$medication->id}"];

    ($this->pay)($records)->assertSessionHas('success', 'Оплачено записей: 2.');

    ($this->pay)($records)
        ->assertRedirect(route('cashier.index'))
        ->assertSessionMissing('success');

    expect(session('error'))->toContain("Услуга №{$service->id} — Оплачена", "Лист назначения №{$medication->id} — Принят в посту")
        ->and($service->fresh()->status)->toBe(ServiceStatus::Paid)
        ->and($medication->fresh()->status)->toBe(PatientMedicationStatus::ReceivedAtStation);
});

it('pays nothing when one of the ticked orders is not waiting for payment', function (string $status): void {
    $payable = PatientService::factory()->confirmed()->for($this->patient)->create();
    $notPayable = PatientService::factory()->for($this->patient)->create(['status' => $status]);

    ($this->pay)(["service:{$payable->id}", "service:{$notPayable->id}"])->assertSessionMissing('success');

    expect(session('error'))->toContain("Услуга №{$notPayable->id}")
        ->and($payable->fresh()->status)->toBe(ServiceStatus::Confirmed)
        ->and($notPayable->fresh()->status->value)->toBe($status);
})->with([
    'not confirmed by the registrar yet' => 'assigned',
    'cancelled' => 'cancelled',
    'already done' => 'completed',
]);

it('pays nothing when an order of another patient is smuggled into the payment', function (): void {
    $own = PatientService::factory()->confirmed()->for($this->patient)->create();
    $foreign = PatientProcedure::factory()->confirmed()->create();

    ($this->pay)(["service:{$own->id}", "procedure:{$foreign->id}"])
        ->assertSessionHas('error', 'Оплата не проведена: в одной оплате могут быть записи только одного пациента.');

    expect($own->fresh()->status)->toBe(ServiceStatus::Confirmed)
        ->and($foreign->fresh()->status)->toBe(ServiceStatus::Confirmed);
});

it('rejects records of an unknown kind, malformed or missing ones', function (array $records, string $errorKey): void {
    $service = PatientService::factory()->confirmed()->for($this->patient)->create();

    ($this->pay)([...$records, "service:{$service->id}"])
        ->assertRedirect(route('cashier.index'))
        ->assertSessionHasErrors($errorKey);

    expect($service->fresh()->status)->toBe(ServiceStatus::Confirmed);
})->with([
    'unknown kind' => [['equipment:1'], 'records.0'],
    'no id' => [['service'], 'records.0'],
    'not an id' => [['service:1;drop'], 'records.0'],
    'not a string' => [[['service:1']], 'records.0'],
    'missing order' => [['procedure:999999'], 'records'],
    'missing order with the largest id accepted' => [['medication:999999999999999999'], 'records'],
]);

it('rejects a payment that is not a list of records, and answers an AJAX call with 422', function (): void {
    $service = PatientService::factory()->confirmed()->for($this->patient)->create();

    actingAs($this->cashier, 'admin')
        ->from(route('cashier.index'))
        ->put(route('cashier.updateAll'), ['records' => "service:{$service->id}"])
        ->assertRedirect(route('cashier.index'))
        ->assertSessionHasErrors('records');

    actingAs($this->cashier, 'admin')
        ->putJson(route('cashier.updateAll'), ['records' => ["service:{$service->id}", 'service']])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('records.1');

    expect($service->fresh()->status)->toBe(ServiceStatus::Confirmed);
});

it('asks to tick something when nothing is ticked', function (): void {
    ($this->pay)([])->assertSessionHasErrors(['records' => 'Выберите записи для оплаты.']);
});

it('keeps the registrar and a view-only account away from payments', function (Closure $admin): void {
    seed(PermissionSeeder::class);
    $service = PatientService::factory()->confirmed()->for($this->patient)->create();

    ($this->pay)(["service:{$service->id}"], $admin())->assertForbidden();

    expect($service->fresh()->status)->toBe(ServiceStatus::Confirmed);
})->with([
    // Passed as closures: the accounts are created once the permissions are seeded.
    'registrar' => fn () => Admin::factory()->role('Регистратор')->create(),
    'cashier page viewer' => fn (): Admin => adminWithPermissions('view_cashier'),
]);

/** What the cashier's "Оплачено" filter and the dashboard's "Оплачено сегодня" read for the day. */
function paidOn(CarbonInterface $day): string
{
    $totals = app(CashierItemsQuery::class)->totals(new CashierFilter(state: PaymentState::Paid, date: $day->toImmutable()));

    // Amount::format() groups the digits with no-break spaces.
    return str_replace("\u{00A0}", ' ', "{$totals->orders}: {$totals->sums->format()}");
}

it('keeps a paid order among the paid ones once the service is carried out or the visit took place', function (): void {
    // The paid filter matched the "paid" status alone: as soon as the patient got the service
    // or saw the doctor, the payment dropped out of the paid list and the day's paid total.
    $service = PatientService::factory()->confirmed()->for($this->patient)->create(['price' => '100000.00', 'currency' => 'UZS']);
    $reception = Reception::factory()->confirmed()->for($this->patient)->create(['price' => '50000.00', 'currency' => 'UZS']);

    ($this->pay)(["service:{$service->id}", "reception:{$reception->id}"])->assertSessionHas('success');

    expect(paidOn(today()))->toBe('2: 150 000,00 UZS');

    $clinician = adminWithPermissions('edit_patient_services', 'change_receptions_status');
    actingAs($clinician, 'admin')->patchJson(route('api.patient-services.update-status', $service), ['status' => 'in_process'])->assertOk();
    actingAs($clinician, 'admin')->putJson(route('api.receptions.update-status', $reception), ['status' => 'done'])->assertOk();

    expect(paidOn(today()))->toBe('2: 150 000,00 UZS');

    actingAs($this->cashier, 'admin')
        ->get(route('cashier.index', ['status' => 'paid']))
        ->assertOk()
        ->assertSeeHtml(['В процессе', 'Выполнен']);
});

it('counts a payment on the day it was taken, not on the day of the order', function (): void {
    // An appointment paid today for next week counted next week, so the day's paid total never
    // matched the cash drawer.
    $nextWeek = today()->addWeek();
    $reception = Reception::factory()->confirmed()->for($this->patient)->create(['date' => $nextWeek->toDateString(), 'price' => '50000.00', 'currency' => 'UZS']);

    $this->travelTo(today()->setTime(16, 45));
    ($this->pay)(["reception:{$reception->id}"])->assertSessionHas('success');

    expect($reception->fresh()->paid_at?->toDateTimeString())->toBe(today()->setTime(16, 45)->toDateTimeString())
        ->and(paidOn(today()))->toBe('1: 50 000,00 UZS')
        ->and(paidOn($nextWeek))->toBe('0: 0,00 UZS');
});

it('counts a paid prescription that was returned afterwards, which is not refunded', function (): void {
    $medication = PatientMedication::factory()->sentToFloor()->for($this->patient)->create(['price' => '7500.00']);

    ($this->pay)(["medication:{$medication->id}"])->assertSessionHas('success');
    $medication->update(['status' => PatientMedicationStatus::Returned]);

    expect(paidOn(today()))->toBe('1: 7 500,00 UZS');
});
