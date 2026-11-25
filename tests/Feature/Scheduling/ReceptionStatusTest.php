<?php

use App\Enums\ReceptionStatus;
use App\Models\ExaminationMap;
use App\Models\Reception;
use App\Models\Staff;
use Tests\Feature\Scheduling\SchedulingFixtures;

use function Pest\Laravel\actingAs;

/*
| The appointment view and status change of the reception calendars.
*/

beforeEach(function (): void {
    $this->registrar = adminWithPermissions('change_receptions_status', 'view_appointment_receptions');
});

it('cancels an appointment once and answers a repeated cancel without changing anything', function (): void {
    $reception = Reception::factory()->create();

    foreach ([1, 2] as $attempt) {
        actingAs($this->registrar, 'admin')
            ->putJson("/api/reception-status-update/{$reception->id}", ['status' => 'cancelled'])
            ->assertOk()
            ->assertJsonPath('status', 'cancelled');
    }

    expect($reception->fresh()->status)->toBe(ReceptionStatus::Cancelled);
});

it('refuses a status change the appointment lifecycle does not allow', function (string $from, string $to): void {
    $reception = Reception::factory()->create(['status' => $from]);

    actingAs($this->registrar, 'admin')
        ->putJson("/api/reception-status-update/{$reception->id}", ['status' => $to])
        ->assertUnprocessable()
        ->assertJsonStructure(['message']);

    expect($reception->fresh()->status->value)->toBe($from);
})->with([
    'reopening a cancelled one' => ['cancelled', 'new'],
    'cancelling a finished visit' => ['done', 'cancelled'],
    'cancelling a paid one, which would be a refund' => ['paid', 'cancelled'],
    'skipping the payment' => ['confirmed', 'done'],
]);

it('leaves confirmation and payment to the registrar and cash desk screens', function (string $status): void {
    // The calendars only cancel here, yet change_receptions_status alone could mark an appointment
    // paid without the cash desk (accept_payments) or confirmed without the registrar.
    $reception = Reception::factory()->create();

    actingAs($this->registrar, 'admin')
        ->putJson("/api/reception-status-update/{$reception->id}", ['status' => $status])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');

    expect($reception->fresh()->status)->toBe(ReceptionStatus::New);
})->with(['confirmed', 'paid']);

it('frees the slot of a cancelled appointment for a new booking', function (): void {
    $doctor = SchedulingFixtures::doctor();
    $reception = SchedulingFixtures::appointment($doctor, '10:00', '10:20');
    $admin = adminWithPermissions('change_receptions_status', 'create_receptions');

    actingAs($admin, 'admin')
        ->putJson("/api/reception-status-update/{$reception->id}", ['status' => 'cancelled'])
        ->assertOk();

    actingAs($admin, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($doctor, '10:00', '10:20'))
        ->assertCreated();
});

it('shows the appointment with its status label and whether it can be cancelled', function (string $status, string $label, bool $canCancel): void {
    $reception = Reception::factory()->create(['status' => $status]);

    actingAs($this->registrar, 'admin')
        ->getJson("/api/receptions/{$reception->id}")
        ->assertOk()
        ->assertJsonPath('status', $status)
        ->assertJsonPath('status_label', $label)
        ->assertJsonPath('can_cancel', $canCancel)
        ->assertJsonPath('patient_id', $reception->patient_id)
        ->assertJsonPath('patient_name', $reception->patient->full_name);
})->with([
    ['confirmed', 'Подтверждён', true],
    ['paid', 'Оплачен', false],
    ['done', 'Выполнен', false],
    ['cancelled', 'Отменён', false],
]);

it('shows exactly what the appointment dialog prints', function (): void {
    $doctor = Staff::factory()->doctor()->create();
    $reception = Reception::factory()->for($doctor)->create([
        'date' => '2026-11-20',
        'start_time' => '10:00',
        'end_time' => '10:20',
        'comment' => 'Повторный приём',
    ]);

    actingAs($this->registrar, 'admin')
        ->getJson("/api/receptions/{$reception->id}")
        ->assertOk()
        ->assertExactJson([
            'id' => $reception->id,
            'date' => '2026-11-20',
            'start_time' => '10:00:00',
            'end_time' => '10:20:00',
            'comment' => 'Повторный приём',
            'status' => 'new',
            'status_label' => 'Новый',
            'can_cancel' => true,
            'patient_id' => $reception->patient_id,
            'patient_name' => $reception->patient->full_name,
            'staff_name' => $doctor->full_name,
            'position' => 'Доктор',
        ]);
});

it('names the patient of an appointment kept only on its stay', function (): void {
    // receptions.patient_id is nullable: older appointments name the patient through the stay.
    $map = ExaminationMap::factory()->create();
    $reception = Reception::factory()->create(['patient_id' => null, 'examination_map_id' => $map->id]);

    actingAs($this->registrar, 'admin')
        ->getJson("/api/receptions/{$reception->id}")
        ->assertOk()
        ->assertJsonPath('patient_id', null)
        ->assertJsonPath('patient_name', $map->patient->full_name);
});

it('shows and cancels on a doctor\'s account only the doctor\'s own appointments', function (Closure $doctorAccount): void {
    // The list showed a doctor only their own appointments, but the dialog opened and cancelled any by id.
    $doctorAdmin = $doctorAccount('view_appointment_receptions', 'change_receptions_status');
    $own = SchedulingFixtures::appointment($doctorAdmin->staff, '09:00', '09:20');
    $ofColleague = SchedulingFixtures::appointment(SchedulingFixtures::doctor(), '09:00', '09:20');
    $refusal = 'Врач работает только со своим календарём приёмов.';

    actingAs($doctorAdmin, 'admin')->getJson("/api/receptions/{$ofColleague->id}")->assertForbidden()->assertJsonPath('message', $refusal);
    actingAs($doctorAdmin, 'admin')
        ->putJson("/api/reception-status-update/{$ofColleague->id}", ['status' => 'cancelled'])
        ->assertForbidden()
        ->assertJsonPath('message', $refusal);

    expect($ofColleague->fresh()->status)->toBe(ReceptionStatus::New);

    actingAs($doctorAdmin, 'admin')->getJson("/api/receptions/{$own->id}")->assertOk()->assertJsonPath('id', $own->id);
    actingAs($doctorAdmin, 'admin')
        ->putJson("/api/reception-status-update/{$own->id}", ['status' => 'cancelled'])
        ->assertOk()
        ->assertJsonPath('status', 'cancelled');
})->with(SchedulingFixtures::doctorAccounts());

it('shows and cancels any appointment on an account without a doctor profile', function (?int $position): void {
    $account = SchedulingFixtures::account($position, 'view_appointment_receptions', 'change_receptions_status');
    $reception = SchedulingFixtures::appointment(SchedulingFixtures::doctor(), '09:00', '09:20');

    actingAs($account, 'admin')->getJson("/api/receptions/{$reception->id}")->assertOk();
    actingAs($account, 'admin')
        ->putJson("/api/reception-status-update/{$reception->id}", ['status' => 'cancelled'])
        ->assertOk()
        ->assertJsonPath('status', 'cancelled');
})->with(SchedulingFixtures::WITHOUT_DOCTOR_PROFILE);

it('forbids changing the status without change_receptions_status', function (): void {
    $reception = Reception::factory()->create();

    actingAs(adminWithPermissions('view_appointment_receptions'), 'admin')
        ->putJson("/api/reception-status-update/{$reception->id}", ['status' => 'cancelled'])
        ->assertForbidden();

    expect($reception->fresh()->status)->toBe(ReceptionStatus::New);
});
