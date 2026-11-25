<?php

use App\Enums\PatientMedicationStatus;
use App\Enums\ReceptionStatus;
use App\Enums\ServiceStatus;
use App\Models\PatientMedication;
use App\Models\PatientProcedure;
use App\Models\PatientService;
use App\Models\Reception;
use App\Services\Billing\BillableType;

it('maps every kind of order to its own model, not to a price list', function (): void {
    expect(BillableType::Service->modelClass())->toBe(PatientService::class)
        ->and(BillableType::Reception->modelClass())->toBe(Reception::class)
        ->and(BillableType::Procedure->modelClass())->toBe(PatientProcedure::class)
        ->and(BillableType::Medication->modelClass())->toBe(PatientMedication::class);
});

it('moves services and procedures from assigned to confirmed to paid', function (BillableType $type): void {
    expect($type->orderedStatus())->toBe(ServiceStatus::Assigned)
        ->and($type->payableStatus())->toBe(ServiceStatus::Confirmed)
        ->and($type->paidStatus())->toBe(ServiceStatus::Paid);
})->with([BillableType::Service, BillableType::Procedure]);

it('moves receptions from new to confirmed to paid', function (): void {
    expect(BillableType::Reception->orderedStatus())->toBe(ReceptionStatus::New)
        ->and(BillableType::Reception->payableStatus())->toBe(ReceptionStatus::Confirmed)
        ->and(BillableType::Reception->paidStatus())->toBe(ReceptionStatus::Paid);
});

it('sends prescriptions to the floor on confirmation and to the nurse station on payment', function (): void {
    expect(BillableType::Medication->orderedStatus())->toBe(PatientMedicationStatus::Ordered)
        ->and(BillableType::Medication->payableStatus())->toBe(PatientMedicationStatus::SentToFloor)
        ->and(BillableType::Medication->paidStatus())->toBe(PatientMedicationStatus::ReceivedAtStation);
});

it('never confirms or pays an order into the status it already has', function (BillableType $type): void {
    $steps = [$type->orderedStatus(), $type->payableStatus(), $type->paidStatus()];

    expect(array_unique(array_map(fn (BackedEnum $status) => $status->value, $steps)))->toHaveCount(3);
})->with(BillableType::cases());

it('only takes steps that the lifecycle of each kind of order allows', function (BillableType $type): void {
    // The statuses belong to the clinical, scheduling and stock modules: billing must not skip a step.
    expect($type->orderedStatus()->canTransitionTo($type->payableStatus()))->toBeTrue()
        ->and($type->payableStatus()->canTransitionTo($type->paidStatus()))->toBeTrue();
})->with(BillableType::cases());

it('counts an order as paid from its payment on, while it stands for that payment', function (): void {
    // The paid list matched the paid status alone, so an order dropped out of it once carried out.
    expect(BillableType::Service->paidStatuses())->toBe([ServiceStatus::Paid, ServiceStatus::InProcess, ServiceStatus::Completed])
        ->and(BillableType::Procedure->paidStatuses())->toBe(BillableType::Service->paidStatuses())
        ->and(BillableType::Reception->paidStatuses())->toBe([ReceptionStatus::Paid, ReceptionStatus::Done])
        // A prescription is returned from sent_to_floor (unpaid) as well: only its paid_at tells.
        ->and(BillableType::Medication->paidStatuses())->toBe([PatientMedicationStatus::ReceivedAtStation, PatientMedicationStatus::Completed]);
});

it('reaches every paid status from the one the cashier sets', function (BillableType $type): void {
    $paid = $type->paidStatus();

    expect($type->paidStatuses()[0])->toBe($paid);

    foreach (array_slice($type->paidStatuses(), 1) as $later) {
        $reachable = $paid->canTransitionTo($later)
            || array_filter($paid->nextStatuses(), fn (BackedEnum $next): bool => $next->canTransitionTo($later)) !== [];

        expect($reachable)->toBeTrue("{$later->value} after {$paid->value}");
    }
})->with(BillableType::cases());

it('names the tables the cashier list reads', function (): void {
    expect(array_map(fn (BillableType $type): string => $type->table(), BillableType::cases()))
        ->toBe(['patient_services', 'receptions', 'patient_procedures', 'patient_medications']);
});
