<?php

use App\Enums\PatientMedicationStatus;
use App\Enums\ReceptionStatus;
use App\Enums\ServiceStatus;
use App\Models\ExaminationMap;
use App\Models\MainWarehouse;
use App\Models\MainWarehouseMedication;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\PatientMedication;
use App\Models\PatientProcedure;
use App\Models\PatientService;
use App\Models\Reception;
use App\Models\StockMovement;
use App\Services\Clinical\PatientMedicationService;
use Database\Seeders\WarehouseSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

/*
| "Подтвердить" in the registrar card of the patient page: the patient's orders move on to the cashier.
*/

/**
 * A prescription of a drug that has a batch of the given size in the main warehouse, which serves
 * outpatients and departments without a warehouse of their own.
 *
 * @param  array<string, mixed>  $attributes
 */
function billingPrescriptionInStock(Patient $patient, int $inStock = 100, array $attributes = []): PatientMedication
{
    $drug = Medication::factory()->create();

    MainWarehouseMedication::factory()->create([
        'main_warehouse_id' => MainWarehouse::MAIN,
        'medication_id' => $drug->id,
        'quantity' => $inStock,
    ]);

    return PatientMedication::factory()->for($patient)->create(['medication_id' => $drug->id, ...$attributes]);
}

beforeEach(function (): void {
    seed(WarehouseSeeder::class);

    $this->registrar = adminWithPermissions('confirm_patient_registration');
    $this->patient = Patient::factory()->create();

    $this->confirm = fn () => actingAs($this->registrar, 'admin')
        ->from(route('patients.show', $this->patient))
        ->post(route('registrar.confirm', $this->patient));
});

it('moves every kind of order from ordered to waiting for payment', function (): void {
    $service = PatientService::factory()->for($this->patient)->create(['status' => 'assigned']);
    $reception = Reception::factory()->for($this->patient)->create(['status' => 'new']);
    $procedure = PatientProcedure::factory()->for($this->patient)->create(['status' => 'assigned']);
    $medication = billingPrescriptionInStock($this->patient);

    ($this->confirm)()
        ->assertRedirect(route('patients.show', $this->patient))
        ->assertSessionHas('success', 'Подтверждено записей: 4.');

    expect($service->fresh()->status)->toBe(ServiceStatus::Confirmed)
        ->and($reception->fresh()->status)->toBe(ReceptionStatus::Confirmed)
        ->and($procedure->fresh()->status)->toBe(ServiceStatus::Confirmed)
        ->and($medication->fresh()->status)->toBe(PatientMedicationStatus::SentToFloor);
});

it('takes the prescribed drug out of the warehouse when it is sent to the floor, and only once', function (): void {
    // The confirmation updated the status alone, so no drug ever left stock.
    $prescription = billingPrescriptionInStock($this->patient, inStock: 10, attributes: ['dosage' => '3']);
    $batch = MainWarehouseMedication::query()->where('medication_id', $prescription->medication_id)->sole();

    ($this->confirm)()->assertSessionHas('success', 'Подтверждено записей: 1.');
    ($this->confirm)()->assertSessionHas('warning');

    expect($prescription->fresh()->status)->toBe(PatientMedicationStatus::SentToFloor)
        ->and($batch->fresh()->quantity)->toBe(7)
        ->and(StockMovement::query()->whereMorphedTo('source', $prescription)->sum('quantity'))->toEqual(-3)
        ->and(StockMovement::query()->pluck('admin_id')->unique()->all())->toBe([$this->registrar->id]);
});

it('records no author for a drug sent to the floor without a signed-in account', function (): void {
    // The ledger takes the author from the admin session: the demo seeder and the console have none.
    $prescription = billingPrescriptionInStock($this->patient, inStock: 10, attributes: ['dosage' => '2']);

    app(PatientMedicationService::class)->sendToFloor($prescription);

    expect(StockMovement::query()->whereMorphedTo('source', $prescription)->pluck('admin_id')->all())->toBe([null]);
});

it('confirms nothing when a prescribed drug is short, and says which one', function (): void {
    $service = PatientService::factory()->for($this->patient)->create(['status' => 'assigned']);
    $inStock = billingPrescriptionInStock($this->patient, inStock: 5, attributes: ['dosage' => '2']);
    $short = billingPrescriptionInStock($this->patient, inStock: 1, attributes: ['dosage' => '2']);

    ($this->confirm)()
        ->assertRedirect(route('patients.show', $this->patient))
        ->assertSessionMissing('success');

    expect(session('error'))->toStartWith('Ничего не подтверждено: Недостаточно')->toContain($short->medication->name)
        ->and($service->fresh()->status)->toBe(ServiceStatus::Assigned)
        ->and($inStock->fresh()->status)->toBe(PatientMedicationStatus::Ordered)
        ->and($short->fresh()->status)->toBe(PatientMedicationStatus::Ordered)
        ->and(MainWarehouseMedication::query()->where('medication_id', $inStock->medication_id)->value('quantity'))->toBe(5)
        ->and(StockMovement::query()->count())->toBe(0);
});

it('confirms the orders of the active examination map and leaves those of a closed stay alone', function (): void {
    $activeMap = ExaminationMap::factory()->for($this->patient)->create();
    $closedMap = ExaminationMap::factory()->discharged()->for($this->patient)->create();

    $current = PatientService::factory()->for($this->patient)->create(['status' => 'assigned', 'examination_map_id' => $activeMap->id]);
    $currentMedication = billingPrescriptionInStock($this->patient, attributes: ['examination_map_id' => $activeMap->id]);
    $leftOver = PatientProcedure::factory()->for($this->patient)->create(['status' => 'assigned', 'examination_map_id' => $closedMap->id]);
    $leftOverMedication = billingPrescriptionInStock($this->patient, attributes: ['examination_map_id' => $closedMap->id]);

    ($this->confirm)()->assertSessionHas('success', 'Подтверждено записей: 2.');

    expect($current->fresh()->status)->toBe(ServiceStatus::Confirmed)
        ->and($currentMedication->fresh()->status)->toBe(PatientMedicationStatus::SentToFloor)
        ->and($leftOver->fresh()->status)->toBe(ServiceStatus::Assigned)
        ->and($leftOverMedication->fresh()->status)->toBe(PatientMedicationStatus::Ordered);
});

it('leaves orders in any other state and the orders of other patients untouched', function (): void {
    $paid = PatientService::factory()->for($this->patient)->create(['status' => 'paid']);
    $cancelled = Reception::factory()->for($this->patient)->create(['status' => 'cancelled']);
    $inProcess = PatientProcedure::factory()->for($this->patient)->create(['status' => 'in_process']);
    $returned = PatientMedication::factory()->for($this->patient)->create(['status' => 'returned']);
    $otherPatients = PatientService::factory()->create(['status' => 'assigned']);
    $otherPatientsPrescription = billingPrescriptionInStock(Patient::factory()->create());

    ($this->confirm)()->assertSessionHas('warning');

    expect($paid->fresh()->status)->toBe(ServiceStatus::Paid)
        ->and($cancelled->fresh()->status)->toBe(ReceptionStatus::Cancelled)
        ->and($inProcess->fresh()->status)->toBe(ServiceStatus::InProcess)
        ->and($returned->fresh()->status)->toBe(PatientMedicationStatus::Returned)
        ->and($otherPatients->fresh()->status)->toBe(ServiceStatus::Assigned)
        ->and($otherPatientsPrescription->fresh()->status)->toBe(PatientMedicationStatus::Ordered);
});

it('is idempotent: a repeated confirmation changes nothing and says so', function (): void {
    $service = PatientService::factory()->for($this->patient)->create(['status' => 'assigned']);
    $medication = billingPrescriptionInStock($this->patient);

    ($this->confirm)()->assertSessionHas('success', 'Подтверждено записей: 2.');

    ($this->confirm)()
        ->assertRedirect(route('patients.show', $this->patient))
        ->assertSessionMissing('success')
        ->assertSessionHas('warning', 'Нет записей, ожидающих подтверждения.');

    expect($service->fresh()->status)->toBe(ServiceStatus::Confirmed)
        ->and($medication->fresh()->status)->toBe(PatientMedicationStatus::SentToFloor);
});

it('keeps the cashier from confirming', function (): void {
    $service = PatientService::factory()->for($this->patient)->create(['status' => 'assigned']);

    actingAs(adminWithPermissions('view_cashier', 'accept_payments'), 'admin')
        ->post(route('registrar.confirm', $this->patient))
        ->assertForbidden();

    expect($service->fresh()->status)->toBe(ServiceStatus::Assigned);
});
