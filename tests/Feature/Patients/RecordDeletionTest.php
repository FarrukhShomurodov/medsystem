<?php

use App\Enums\ReceptionStatus;
use App\Models\ExaminationMap;
use App\Models\Patient;
use App\Models\PatientMedication;
use App\Models\PatientProcedure;
use App\Models\PatientService;
use App\Models\Reception;
use Illuminate\Database\Eloquent\Model;

use function Pest\Laravel\actingAs;

/*
| Deleting a patient or a stay cascades in the database to all their orders. What was paid at the
| cash desk, what left the warehouse and a closed stay are records that must outlive the mistake
| of registering a patient twice, so such deletions are refused.
*/

/**
 * Orders of every kind that were paid or dispensed, as the kind's name in the refusal => a maker.
 *
 * @return array<string, array{string, Closure(array<string, mixed>): Model}>
 */
function retainedOrders(): array
{
    return [
        'a paid service' => ['оплаченные услуги', fn (array $owner) => PatientService::factory()->create([...$owner, 'status' => 'paid'])],
        'a service carried out after payment' => ['оплаченные услуги', fn (array $owner) => PatientService::factory()->create([...$owner, 'status' => 'completed'])],
        'a paid procedure' => ['оплаченные процедуры', fn (array $owner) => PatientProcedure::factory()->create([...$owner, 'status' => 'paid'])],
        'a paid appointment' => ['оплаченные приёмы', fn (array $owner) => Reception::factory()->create([...$owner, 'status' => 'paid'])],
        'a visit that took place' => ['оплаченные приёмы', fn (array $owner) => Reception::factory()->create([...$owner, 'status' => 'done'])],
        // Paid, then returned: the cash desk took the money and no refund is recorded.
        'a drug paid for, then returned' => ['препараты, выданные со склада', fn (array $owner) => PatientMedication::factory()->create([...$owner, 'status' => 'returned', 'paid_at' => now()])],
        'a drug sent to the floor' => ['препараты, выданные со склада', fn (array $owner) => PatientMedication::factory()->create([...$owner, 'status' => 'sent_to_floor'])],
        'a drug returned to the warehouse' => ['препараты, выданные со склада', fn (array $owner) => PatientMedication::factory()->create([...$owner, 'status' => 'returned'])],
    ];
}

/**
 * One order of every kind that is neither paid nor dispensed.
 *
 * @param  array<string, mixed>  $owner
 * @return list<Model>
 */
function openOrders(array $owner): array
{
    return [
        PatientService::factory()->create([...$owner, 'status' => 'assigned']),
        PatientService::factory()->create([...$owner, 'status' => 'cancelled']),
        PatientProcedure::factory()->create([...$owner, 'status' => 'confirmed']),
        Reception::factory()->create([...$owner, 'status' => 'new']),
        PatientMedication::factory()->create([...$owner, 'status' => 'ordered']),
        PatientMedication::factory()->create([...$owner, 'status' => 'cancelled']),
    ];
}

describe('deleting a patient', function (): void {
    beforeEach(function (): void {
        actingAs(adminWithPermissions('delete_patients', 'list_patients'), 'admin');
        $this->patient = Patient::factory()->create();
    });

    it('is refused while the patient has an order that was paid or dispensed', function (string $kind, Closure $order): void {
        // The cascade used to delete the order with the patient: the cash desk lost a payment.
        $retained = $order(['patient_id' => $this->patient->id]);

        $this->from(route('patients.edit', $this->patient))
            ->delete(route('patients.destroy', $this->patient))
            ->assertRedirect(route('patients.edit', $this->patient))
            ->assertSessionHas('error', "Пациента нельзя удалить: у него есть {$kind}. Эти записи хранятся как финансовые и медицинские документы.");

        expect(Patient::query()->whereKey($this->patient->id)->exists())->toBeTrue()
            ->and($retained->fresh())->not->toBeNull();
    })->with(retainedOrders());

    it('is refused after an appointment was paid, which the calendar can no longer cancel', function (): void {
        // Cancelling the paid appointment took it out of the retained records, and the next
        // deletion took the payment with the patient (receptions.patient_id cascades).
        $reception = Reception::factory()->confirmed()->for($this->patient)->create();

        actingAs(adminWithPermissions('view_cashier', 'accept_payments'), 'admin')
            ->put(route('cashier.updateAll'), ['records' => ["reception:{$reception->id}"]])
            ->assertSessionHas('success');

        actingAs(adminWithPermissions('change_receptions_status'), 'admin')
            ->putJson(route('api.receptions.update-status', $reception), ['status' => 'cancelled'])
            ->assertUnprocessable();

        actingAs(adminWithPermissions('delete_patients', 'list_patients'), 'admin')
            ->delete(route('patients.destroy', $this->patient))
            ->assertSessionHas('error');

        expect($reception->fresh()?->status)->toBe(ReceptionStatus::Paid)
            ->and(Patient::query()->whereKey($this->patient->id)->exists())->toBeTrue();
    });

    it('is refused while the patient has a closed stay, naming everything that is kept', function (): void {
        $stay = ExaminationMap::factory()->discharged()->for($this->patient)->create();
        PatientService::factory()->create(['patient_id' => $this->patient->id, 'examination_map_id' => $stay->id, 'status' => 'paid']);
        PatientMedication::factory()->create(['patient_id' => $this->patient->id, 'status' => 'completed']);

        $this->delete(route('patients.destroy', $this->patient))
            ->assertSessionHas('error', 'Пациента нельзя удалить: у него есть оплаченные услуги, препараты, выданные со склада, завершённые госпитализации. Эти записи хранятся как финансовые и медицинские документы.');

        expect($stay->fresh())->not->toBeNull();
    });

    it('deletes the patient with an open stay and orders that were neither paid nor dispensed', function (): void {
        $stay = ExaminationMap::factory()->for($this->patient)->create();
        $orders = [
            ...openOrders(['patient_id' => $this->patient->id]),
            ...openOrders(['patient_id' => $this->patient->id, 'examination_map_id' => $stay->id]),
        ];

        $this->delete(route('patients.destroy', $this->patient))
            ->assertRedirectToRoute('patients.index')
            ->assertSessionHas('success', 'Пациент успешно удалён.');

        expect(Patient::query()->whereKey($this->patient->id)->exists())->toBeFalse()
            ->and($stay->fresh())->toBeNull()
            ->and(collect($orders)->map->fresh()->filter()->all())->toBe([]);
    });

    it('does not look at the paid orders of other patients', function (): void {
        PatientService::factory()->create(['status' => 'paid']);

        $this->delete(route('patients.destroy', $this->patient))->assertSessionHas('success');

        expect(Patient::query()->whereKey($this->patient->id)->exists())->toBeFalse();
    });
});

describe('deleting a stay', function (): void {
    beforeEach(function (): void {
        actingAs(adminWithPermissions('delete_maps'), 'admin');
        $this->stay = ExaminationMap::factory()->create();
        $this->ofStay = ['patient_id' => $this->stay->patient_id, 'examination_map_id' => $this->stay->id];
    });

    it('is refused while the stay has an order that was paid or dispensed', function (string $kind, Closure $order): void {
        $retained = $order($this->ofStay);

        $this->delete(route('maps.destroy', $this->stay))
            ->assertRedirectToRoute('patients.show', $this->stay->patient_id)
            ->assertSessionHas('error', "Карту обследования нельзя удалить: в ней есть {$kind}. Эти записи хранятся как финансовые и медицинские документы.");

        expect($this->stay->fresh())->not->toBeNull()
            ->and($retained->fresh())->not->toBeNull();
    })->with(retainedOrders());

    it('deletes the stay with its open orders, keeping the paid outpatient orders of the patient', function (): void {
        $orders = openOrders($this->ofStay);
        $outpatient = PatientService::factory()->create(['patient_id' => $this->stay->patient_id, 'status' => 'paid']);

        $this->delete(route('maps.destroy', $this->stay))
            ->assertRedirectToRoute('patients.show', $this->stay->patient_id)
            ->assertSessionHas('success', 'Карта обследования успешно удалена!');

        expect($this->stay->fresh())->toBeNull()
            ->and(collect($orders)->map->fresh()->filter()->all())->toBe([])
            ->and($outpatient->fresh())->not->toBeNull();
    });
});
