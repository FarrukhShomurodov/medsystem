<?php

use App\Enums\PatientMedicationStatus;
use App\Enums\SupplyRequestStatus;
use App\Enums\TransferStatus;

it('lets a prescription be cancelled only before the drug leaves the warehouse', function (): void {
    expect(PatientMedicationStatus::Ordered->canTransitionTo(PatientMedicationStatus::Cancelled))->toBeTrue()
        ->and(PatientMedicationStatus::SentToFloor->canTransitionTo(PatientMedicationStatus::Cancelled))->toBeFalse()
        ->and(PatientMedicationStatus::SentToFloor->canTransitionTo(PatientMedicationStatus::Returned))->toBeTrue()
        ->and(PatientMedicationStatus::ReceivedAtStation->canTransitionTo(PatientMedicationStatus::Returned))->toBeTrue()
        ->and(PatientMedicationStatus::Ordered->canTransitionTo(PatientMedicationStatus::Returned))->toBeFalse();
});

it('moves a prescription forward one step at a time', function (): void {
    expect(PatientMedicationStatus::Ordered->nextStatuses())->toBe([PatientMedicationStatus::SentToFloor, PatientMedicationStatus::Cancelled])
        ->and(PatientMedicationStatus::SentToFloor->canTransitionTo(PatientMedicationStatus::ReceivedAtStation))->toBeTrue()
        ->and(PatientMedicationStatus::ReceivedAtStation->canTransitionTo(PatientMedicationStatus::Completed))->toBeTrue()
        ->and(PatientMedicationStatus::Ordered->canTransitionTo(PatientMedicationStatus::Completed))->toBeFalse()
        ->and(PatientMedicationStatus::SentToFloor->canTransitionTo(PatientMedicationStatus::SentToFloor))->toBeFalse();
});

it('treats given, returned and cancelled prescriptions as final', function (): void {
    expect(PatientMedicationStatus::finalValues())->toBe(['completed', 'returned', 'cancelled']);

    foreach ([PatientMedicationStatus::Completed, PatientMedicationStatus::Returned, PatientMedicationStatus::Cancelled] as $status) {
        expect($status->isFinal())->toBeTrue()
            ->and($status->nextStatuses())->toBe([]);
    }
});

it('completes or cancels a transfer once, from pending only', function (): void {
    expect(TransferStatus::Pending->canTransitionTo(TransferStatus::Completed))->toBeTrue()
        ->and(TransferStatus::Pending->canTransitionTo(TransferStatus::Canceled))->toBeTrue()
        ->and(TransferStatus::Completed->canTransitionTo(TransferStatus::Completed))->toBeFalse()
        ->and(TransferStatus::Canceled->canTransitionTo(TransferStatus::Completed))->toBeFalse()
        ->and(TransferStatus::Pending->isEditable())->toBeTrue()
        ->and(TransferStatus::Completed->isEditable())->toBeFalse();
});

it('decides a supply request once: approved or rejected, never both and never twice', function (): void {
    expect(SupplyRequestStatus::Pending->nextStatuses())->toBe([SupplyRequestStatus::Approved, SupplyRequestStatus::Rejected])
        ->and(SupplyRequestStatus::Approved->canTransitionTo(SupplyRequestStatus::Approved))->toBeFalse()
        ->and(SupplyRequestStatus::Approved->canTransitionTo(SupplyRequestStatus::Rejected))->toBeFalse()
        ->and(SupplyRequestStatus::Rejected->canTransitionTo(SupplyRequestStatus::Approved))->toBeFalse()
        ->and(SupplyRequestStatus::Rejected->canTransitionTo(SupplyRequestStatus::Rejected))->toBeFalse()
        ->and(SupplyRequestStatus::Approved->isFinal())->toBeTrue()
        ->and(SupplyRequestStatus::Rejected->isFinal())->toBeTrue();
});
