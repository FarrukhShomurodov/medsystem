<?php

use App\Enums\ReceptionStatus;

it('moves an appointment from booking through confirmation and payment to the visit', function (): void {
    expect(ReceptionStatus::New->canTransitionTo(ReceptionStatus::Confirmed))->toBeTrue()
        ->and(ReceptionStatus::Confirmed->canTransitionTo(ReceptionStatus::Paid))->toBeTrue()
        ->and(ReceptionStatus::Paid->canTransitionTo(ReceptionStatus::Done))->toBeTrue()
        ->and(ReceptionStatus::New->canTransitionTo(ReceptionStatus::Paid))->toBeTrue();
});

it('allows cancelling until the appointment is paid', function (ReceptionStatus $status, bool $cancellable): void {
    // A paid appointment could be cancelled: the payment then vanished from the cash figures and
    // no longer kept the patient record from being deleted. Undoing a payment is a refund.
    expect($status->canTransitionTo(ReceptionStatus::Cancelled))->toBe($cancellable);
})->with([
    [ReceptionStatus::New, true],
    [ReceptionStatus::Confirmed, true],
    [ReceptionStatus::Paid, false],
    [ReceptionStatus::Done, false],
    [ReceptionStatus::Cancelled, false],
]);

it('stands for its payment from the payment to the visit', function (): void {
    expect(array_values(array_filter(ReceptionStatus::cases(), fn (ReceptionStatus $status): bool => $status->isPaid())))
        ->toBe([ReceptionStatus::Paid, ReceptionStatus::Done]);
});

it('never goes back or skips the payment', function (): void {
    expect(ReceptionStatus::Cancelled->canTransitionTo(ReceptionStatus::New))->toBeFalse()
        ->and(ReceptionStatus::Paid->canTransitionTo(ReceptionStatus::Confirmed))->toBeFalse()
        ->and(ReceptionStatus::Confirmed->canTransitionTo(ReceptionStatus::Done))->toBeFalse()
        ->and(ReceptionStatus::New->canTransitionTo(ReceptionStatus::New))->toBeFalse();
});

it('ends with a visit or a cancellation, and only a cancellation frees the slot', function (): void {
    expect(array_filter(ReceptionStatus::cases(), fn (ReceptionStatus $status): bool => $status->isFinal()))
        ->toBe([3 => ReceptionStatus::Done, 4 => ReceptionStatus::Cancelled])
        ->and(array_filter(ReceptionStatus::cases(), fn (ReceptionStatus $status): bool => ! $status->holdsSlot()))
        ->toBe([4 => ReceptionStatus::Cancelled])
        ->and(ReceptionStatus::initial())->toBe([ReceptionStatus::New, ReceptionStatus::Paid]);
});
